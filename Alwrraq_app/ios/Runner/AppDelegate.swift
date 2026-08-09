import Flutter
import UIKit
import WebKit

@main
@objc class AppDelegate: FlutterAppDelegate, FlutterImplicitEngineDelegate {
  static let incomingShareNotification = Notification.Name("AlwrraqIncomingShare")
  private let appGroup = "group.com.alwrraq.app"
  private let shareManifestName = "pending-share.json"
  private var privacyCover: UIView?
  private var shareChannel: FlutterMethodChannel?
  private var shareObserver: NSObjectProtocol?
  override func application(
    _ application: UIApplication,
    didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
  ) -> Bool {
    return super.application(application, didFinishLaunchingWithOptions: launchOptions)
  }

  func didInitializeImplicitFlutterEngine(_ engineBridge: FlutterImplicitEngineBridge) {
    GeneratedPluginRegistrant.register(with: engineBridge.pluginRegistry)

    guard let registrar = engineBridge.pluginRegistry.registrar(forPlugin: "AlwrraqDownloads") else {
      return
    }

    let channel = FlutterMethodChannel(
      name: "alwrraq/downloads",
      binaryMessenger: registrar.messenger()
    )

    channel.setMethodCallHandler { [weak self] call, result in
      guard call.method == "download" else {
        result(FlutterMethodNotImplemented)
        return
      }
      guard
        let arguments = call.arguments as? [String: Any],
        let urlText = arguments["url"] as? String,
        let url = URL(string: urlText)
      else {
        result(FlutterError(code: "invalid_url", message: "رابط التحميل غير صالح", details: nil))
        return
      }

      let requestedName = (arguments["fileName"] as? String) ?? "alwrraq-file"
      let fileName = self?.safeFileName(requestedName) ?? "alwrraq-file"

      WKWebsiteDataStore.default().httpCookieStore.getAllCookies { cookies in
        var request = URLRequest(url: url)
        let matchingCookies = cookies.filter { cookie in
          url.host?.hasSuffix(cookie.domain.trimmingCharacters(in: CharacterSet(charactersIn: "."))) == true
        }
        let cookieHeaders = HTTPCookie.requestHeaderFields(with: matchingCookies)
        cookieHeaders.forEach { request.setValue($1, forHTTPHeaderField: $0) }
        request.setValue("Alwrraq iOS App", forHTTPHeaderField: "User-Agent")

        let task = URLSession.shared.downloadTask(with: request) { temporaryUrl, _, error in
          guard let temporaryUrl, error == nil else { return }

          do {
            let documents = FileManager.default.urls(for: .documentDirectory, in: .userDomainMask)[0]
            let destination = self?.availableDestination(in: documents, fileName: fileName)
              ?? documents.appendingPathComponent(fileName)
            try FileManager.default.moveItem(at: temporaryUrl, to: destination)

            DispatchQueue.main.async {
              self?.presentShareSheet(for: destination)
            }
          } catch {
            return
          }
        }
        task.resume()
        result(true)
      }
    }

    guard let securityRegistrar = engineBridge.pluginRegistry.registrar(forPlugin: "AlwrraqSecurity") else {
      return
    }
    let securityChannel = FlutterMethodChannel(
      name: "alwrraq/security",
      binaryMessenger: securityRegistrar.messenger()
    )
    securityChannel.setMethodCallHandler { [weak self] call, result in
      guard call.method == "setSecureScreen",
            let arguments = call.arguments as? [String: Any]
      else {
        result(FlutterMethodNotImplemented)
        return
      }
      let secure = (arguments["secure"] as? Bool) == true
      DispatchQueue.main.async {
        self?.setPrivacyCover(enabled: secure)
        result(nil)
      }
    }

    guard let shareRegistrar = engineBridge.pluginRegistry.registrar(forPlugin: "AlwrraqShare") else {
      return
    }
    let shareFlutterChannel = FlutterMethodChannel(
      name: "alwrraq/share",
      binaryMessenger: shareRegistrar.messenger()
    )
    shareChannel = shareFlutterChannel
    shareFlutterChannel.setMethodCallHandler { [weak self] call, result in
      switch call.method {
      case "getInitialShare":
        result(self?.consumeSharedFiles() ?? [])
      case "getCookies":
        guard
          let arguments = call.arguments as? [String: Any],
          let urlText = arguments["url"] as? String,
          let url = URL(string: urlText)
        else {
          result(nil)
          return
        }
        WKWebsiteDataStore.default().httpCookieStore.getAllCookies { cookies in
          let matching = cookies.filter { cookie in
            url.host?.hasSuffix(
              cookie.domain.trimmingCharacters(in: CharacterSet(charactersIn: "."))
            ) == true
          }
          result(HTTPCookie.requestHeaderFields(with: matching)["Cookie"])
        }
      default:
        result(FlutterMethodNotImplemented)
      }
    }

    shareObserver = NotificationCenter.default.addObserver(
      forName: AppDelegate.incomingShareNotification,
      object: nil,
      queue: .main
    ) { [weak self] _ in
      self?.publishPendingSharedFiles()
    }
  }

  override func application(
    _ app: UIApplication,
    open url: URL,
    options: [UIApplication.OpenURLOptionsKey: Any] = [:]
  ) -> Bool {
    guard url.scheme == "alwrraq", url.host == "share" else {
      return super.application(app, open: url, options: options)
    }
    publishPendingSharedFiles()
    return true
  }

  private func consumeSharedFiles() -> [[String: Any]] {
    guard
      let container = FileManager.default.containerURL(
        forSecurityApplicationGroupIdentifier: appGroup
      )
    else { return [] }

    let manifest = container.appendingPathComponent(shareManifestName)
    guard
      let data = try? Data(contentsOf: manifest),
      let payload = try? JSONSerialization.jsonObject(with: data) as? [[String: Any]]
    else { return [] }

    try? FileManager.default.removeItem(at: manifest)
    return payload
  }

  private func publishPendingSharedFiles() {
    let files = consumeSharedFiles()
    guard !files.isEmpty else { return }
    shareChannel?.invokeMethod("sharedFiles", arguments: files)
  }

  deinit {
    if let shareObserver {
      NotificationCenter.default.removeObserver(shareObserver)
    }
  }

  private func safeFileName(_ fileName: String) -> String {
    let invalidCharacters = CharacterSet(charactersIn: "\\/:*?\"<>|")
    return fileName.components(separatedBy: invalidCharacters).joined(separator: "_")
  }

  private func availableDestination(in directory: URL, fileName: String) -> URL {
    let original = directory.appendingPathComponent(fileName)
    guard FileManager.default.fileExists(atPath: original.path) else { return original }

    let extensionName = original.pathExtension
    let baseName = original.deletingPathExtension().lastPathComponent
    var copyNumber = 2

    while true {
      let candidateName = extensionName.isEmpty
        ? "\(baseName) \(copyNumber)"
        : "\(baseName) \(copyNumber).\(extensionName)"
      let candidate = directory.appendingPathComponent(candidateName)
      if !FileManager.default.fileExists(atPath: candidate.path) {
        return candidate
      }
      copyNumber += 1
    }
  }

  private func presentShareSheet(for fileUrl: URL) {
    guard let root = activeRootViewController() else { return }
    let share = UIActivityViewController(activityItems: [fileUrl], applicationActivities: nil)

    if let popover = share.popoverPresentationController {
      popover.sourceView = root.view
      popover.sourceRect = CGRect(
        x: root.view.bounds.midX,
        y: root.view.bounds.midY,
        width: 1,
        height: 1
      )
    }

    root.present(share, animated: true)
  }

  private func activeRootViewController() -> UIViewController? {
    let windowScene = UIApplication.shared.connectedScenes
      .compactMap { $0 as? UIWindowScene }
      .first { $0.activationState == .foregroundActive }
    var controller = windowScene?.windows.first { $0.isKeyWindow }?.rootViewController

    while let presented = controller?.presentedViewController {
      controller = presented
    }

    return controller
  }

  private func setPrivacyCover(enabled: Bool) {
    guard let window = activeRootViewController()?.view.window else { return }

    NotificationCenter.default.removeObserver(
      self,
      name: UIScreen.capturedDidChangeNotification,
      object: nil
    )

    guard enabled else {
      privacyCover?.removeFromSuperview()
      privacyCover = nil
      return
    }

    let cover = privacyCover ?? {
      let view = UIView(frame: window.bounds)
      view.autoresizingMask = [.flexibleWidth, .flexibleHeight]
      view.backgroundColor = UIColor(red: 0.059, green: 0.09, blue: 0.165, alpha: 1)
      let label = UILabel(frame: view.bounds.insetBy(dx: 24, dy: 24))
      label.autoresizingMask = [.flexibleWidth, .flexibleHeight]
      label.text = "المعاينة محمية حتى إتمام الدفع"
      label.textColor = .white
      label.font = .boldSystemFont(ofSize: 18)
      label.textAlignment = .center
      label.numberOfLines = 0
      view.addSubview(label)
      privacyCover = view
      return view
    }()

    let refreshCover = { [weak self, weak window] in
      guard let self, let window else { return }
      if UIScreen.main.isCaptured {
        if cover.superview == nil { window.addSubview(cover) }
      } else {
        cover.removeFromSuperview()
      }
    }
    NotificationCenter.default.addObserver(
      forName: UIScreen.capturedDidChangeNotification,
      object: nil,
      queue: .main
    ) { _ in refreshCover() }
    refreshCover()
  }
}
