import Flutter
import UIKit

class SceneDelegate: FlutterSceneDelegate {
  override func scene(_ scene: UIScene, openURLContexts URLContexts: Set<UIOpenURLContext>) {
    if URLContexts.contains(where: { context in
      context.url.scheme == "alwrraq" && context.url.host == "share"
    }) {
      NotificationCenter.default.post(
        name: AppDelegate.incomingShareNotification,
        object: nil
      )
      return
    }
    super.scene(scene, openURLContexts: URLContexts)
  }
}
