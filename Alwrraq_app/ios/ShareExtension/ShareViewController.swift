import UIKit

final class ShareViewController: UIViewController {
  private let appGroup = "group.com.alwrraq.app"
  private let manifestName = "pending-share.json"
  private let workQueue = DispatchQueue(label: "com.alwrraq.share-extension.files")
  private var collectedFiles: [[String: Any]] = []

  override func viewDidLoad() {
    super.viewDidLoad()
    view.backgroundColor = .systemBackground

    let spinner = UIActivityIndicatorView(style: .large)
    spinner.translatesAutoresizingMaskIntoConstraints = false
    spinner.startAnimating()

    let label = UILabel()
    label.translatesAutoresizingMaskIntoConstraints = false
    label.text = "جارٍ تجهيز الملفات للورّاق…"
    label.textAlignment = .center
    label.font = .boldSystemFont(ofSize: 17)
    label.numberOfLines = 0

    let stack = UIStackView(arrangedSubviews: [spinner, label])
    stack.translatesAutoresizingMaskIntoConstraints = false
    stack.axis = .vertical
    stack.spacing = 16
    stack.alignment = .center
    view.addSubview(stack)
    NSLayoutConstraint.activate([
      stack.centerXAnchor.constraint(equalTo: view.centerXAnchor),
      stack.centerYAnchor.constraint(equalTo: view.centerYAnchor),
      stack.leadingAnchor.constraint(greaterThanOrEqualTo: view.leadingAnchor, constant: 24),
      stack.trailingAnchor.constraint(lessThanOrEqualTo: view.trailingAnchor, constant: -24),
    ])

    collectAttachments()
  }

  private func collectAttachments() {
    guard
      let items = extensionContext?.inputItems as? [NSExtensionItem],
      let container = FileManager.default.containerURL(
        forSecurityApplicationGroupIdentifier: appGroup
      )
    else {
      finishWithError()
      return
    }

    let importsDirectory = container.appendingPathComponent("SharedImports", isDirectory: true)
    try? FileManager.default.createDirectory(
      at: importsDirectory,
      withIntermediateDirectories: true
    )

    let providers = items.flatMap { $0.attachments ?? [] }
    let group = DispatchGroup()
    for provider in providers {
      guard let identifier = preferredTypeIdentifier(for: provider) else { continue }
      group.enter()
      provider.loadFileRepresentation(forTypeIdentifier: identifier) { [weak self] url, _ in
        defer { group.leave() }
        guard let self, let url else { return }
        self.copySharedFile(
          from: url,
          provider: provider,
          typeIdentifier: identifier,
          to: importsDirectory
        )
      }
    }

    group.notify(queue: .main) { [weak self] in
      self?.saveManifestAndOpenApp(in: container)
    }
  }

  private func preferredTypeIdentifier(for provider: NSItemProvider) -> String? {
    let supported = [
      "com.adobe.pdf",
      "org.openxmlformats.wordprocessingml.document",
      "com.microsoft.word.doc",
      "public.image",
    ]
    return supported.first { provider.hasItemConformingToTypeIdentifier($0) }
  }

  private func copySharedFile(
    from source: URL,
    provider: NSItemProvider,
    typeIdentifier: String,
    to directory: URL
  ) {
    let suggestedName = provider.suggestedName?.trimmingCharacters(in: .whitespacesAndNewlines)
    let sourceExtension = source.pathExtension
    var originalName = suggestedName?.isEmpty == false ? suggestedName! : source.lastPathComponent
    if URL(fileURLWithPath: originalName).pathExtension.isEmpty && !sourceExtension.isEmpty {
      originalName += ".\(sourceExtension)"
    }

    let safeName = originalName.replacingOccurrences(
      of: "[^A-Za-z0-9._\\-\\u0600-\\u06FF ]",
      with: "_",
      options: .regularExpression
    )
    let destination = directory.appendingPathComponent("\(UUID().uuidString)-\(safeName)")

    do {
      try FileManager.default.copyItem(at: source, to: destination)
      let attributes = try FileManager.default.attributesOfItem(atPath: destination.path)
      let size = (attributes[.size] as? NSNumber)?.intValue ?? 0
      let mimeType = mimeType(
        for: typeIdentifier,
        extensionName: destination.pathExtension.lowercased()
      )
      workQueue.sync {
        collectedFiles.append([
          "path": destination.path,
          "name": originalName,
          "mimeType": mimeType,
          "size": size,
        ])
      }
    } catch {
      return
    }
  }

  private func mimeType(for typeIdentifier: String, extensionName: String) -> String {
    if typeIdentifier == "com.adobe.pdf" || extensionName == "pdf" {
      return "application/pdf"
    }
    if typeIdentifier == "com.microsoft.word.doc" || extensionName == "doc" {
      return "application/msword"
    }
    if typeIdentifier == "org.openxmlformats.wordprocessingml.document"
      || extensionName == "docx"
    {
      return "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
    }

    let imageTypes = [
      "jpg": "image/jpeg",
      "jpeg": "image/jpeg",
      "png": "image/png",
      "gif": "image/gif",
      "webp": "image/webp",
      "heic": "image/heic",
      "heif": "image/heif",
      "tif": "image/tiff",
      "tiff": "image/tiff",
      "bmp": "image/bmp",
      "svg": "image/svg+xml",
    ]
    return imageTypes[extensionName] ?? "image/\(extensionName.isEmpty ? "jpeg" : extensionName)"
  }

  private func saveManifestAndOpenApp(in container: URL) {
    guard !collectedFiles.isEmpty else {
      finishWithError()
      return
    }

    do {
      let data = try JSONSerialization.data(withJSONObject: collectedFiles)
      try data.write(to: container.appendingPathComponent(manifestName), options: .atomic)
      guard let url = URL(string: "alwrraq://share") else {
        finishWithError()
        return
      }
      extensionContext?.open(url) { [weak self] opened in
        guard let self else { return }
        if !opened {
          self.openContainingAppFallback(url)
        }
        DispatchQueue.main.asyncAfter(deadline: .now() + 0.35) {
          self.extensionContext?.completeRequest(returningItems: nil)
        }
      }
    } catch {
      finishWithError()
    }
  }

  private func openContainingAppFallback(_ url: URL) {
    let selector = NSSelectorFromString("openURL:")
    var responder: UIResponder? = self
    while let current = responder {
      if current.responds(to: selector) {
        current.perform(selector, with: url)
        return
      }
      responder = current.next
    }
  }

  private func finishWithError() {
    let error = NSError(
      domain: "com.alwrraq.share-extension",
      code: 1,
      userInfo: [NSLocalizedDescriptionKey: "تعذر تجهيز الملفات المشتركة"]
    )
    extensionContext?.cancelRequest(withError: error)
  }
}
