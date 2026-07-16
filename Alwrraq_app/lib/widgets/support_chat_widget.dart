import 'dart:async';

import 'package:flutter/material.dart';

import '../services/api_client.dart';

class SupportChatWidget extends StatefulWidget {
  const SupportChatWidget({super.key});

  @override
  State<SupportChatWidget> createState() => _SupportChatWidgetState();
}

class _SupportChatWidgetState extends State<SupportChatWidget> {
  final _messageController = TextEditingController();
  final _scrollController = ScrollController();
  Timer? _timer;
  bool _isOpen = false;
  bool _isLoading = false;
  int? _conversationId;
  int _unreadCount = 0;
  List<Map<String, dynamic>> _messages = [];

  @override
  void initState() {
    super.initState();
    _refresh();
    _timer = Timer.periodic(const Duration(seconds: 12), (_) => _refresh());
  }

  @override
  void dispose() {
    _timer?.cancel();
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _refresh() async {
    final conversationsData = await ApiClient.chatConversations();
    final conversations = conversationsData['conversations'];
    if (conversations is! List || conversations.isEmpty) return;

    final first = conversations.first;
    final conversationId = _toInt(first['id']);
    if (conversationId == null) return;

    if (!mounted) return;
    setState(() {
      _conversationId = conversationId;
      _unreadCount = _toInt(first['unread_count']) ?? 0;
    });

    if (_isOpen) {
      await _loadMessages(conversationId);
    }
  }

  Future<void> _loadMessages([int? conversationId]) async {
    final id = conversationId ?? _conversationId;
    if (id == null) return;

    setState(() => _isLoading = true);
    final data = await ApiClient.chatMessages(conversationId: id);
    if (!mounted) return;

    final messages = data['messages'];
    setState(() {
      _isLoading = false;
      _unreadCount = 0;
      _messages = messages is List
          ? messages
                .whereType<Map>()
                .map((item) => Map<String, dynamic>.from(item))
                .toList()
          : [];
    });

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.jumpTo(_scrollController.position.maxScrollExtent);
      }
    });
  }

  Future<void> _sendMessage() async {
    final id = _conversationId;
    final message = _messageController.text.trim();
    if (id == null || message.isEmpty) return;

    _messageController.clear();
    final response = await ApiClient.sendChatMessage(
      conversationId: id,
      message: message,
    );

    if (!mounted) return;

    if (response['message'] is Map) {
      await _loadMessages(id);
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(response['message']?.toString() ?? 'تعذر إرسال الرسالة'),
      ),
    );
  }

  void _toggleChat() {
    setState(() => _isOpen = !_isOpen);
    if (!_isOpen) return;
    _refresh();
  }

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Stack(
        children: [
          if (_isOpen)
            Positioned(
              left: 14,
              bottom: 78,
              child: _ChatPanel(
                isLoading: _isLoading,
                messages: _messages,
                controller: _messageController,
                scrollController: _scrollController,
                onClose: _toggleChat,
                onSend: _sendMessage,
              ),
            ),
          Positioned(
            left: 14,
            bottom: 18,
            child: _ChatLauncher(
              unreadCount: _unreadCount,
              onPressed: _toggleChat,
            ),
          ),
        ],
      ),
    );
  }

  int? _toInt(Object? value) {
    if (value is int) return value;
    if (value is num) return value.round();
    return int.tryParse(value?.toString() ?? '');
  }
}

class _ChatLauncher extends StatelessWidget {
  const _ChatLauncher({required this.unreadCount, required this.onPressed});

  final int unreadCount;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return Stack(
      clipBehavior: Clip.none,
      children: [
        ElevatedButton.icon(
          onPressed: onPressed,
          icon: const Icon(Icons.support_agent, size: 20),
          label: const Text('تواصل مع خدمة العملاء'),
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF0F4C81),
            foregroundColor: Colors.white,
            elevation: 12,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
            shape: const StadiumBorder(),
            textStyle: const TextStyle(
              fontFamily: 'Arial',
              fontWeight: FontWeight.w900,
            ),
          ),
        ),
        if (unreadCount > 0)
          Positioned(
            top: -6,
            right: -6,
            child: Container(
              constraints: const BoxConstraints(minWidth: 21),
              height: 21,
              alignment: Alignment.center,
              padding: const EdgeInsets.symmetric(horizontal: 6),
              decoration: BoxDecoration(
                color: const Color(0xFFDC2626),
                borderRadius: BorderRadius.circular(999),
                border: Border.all(color: Colors.white, width: 2),
              ),
              child: Text(
                '$unreadCount',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 11,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
          ),
      ],
    );
  }
}

class _ChatPanel extends StatelessWidget {
  const _ChatPanel({
    required this.isLoading,
    required this.messages,
    required this.controller,
    required this.scrollController,
    required this.onClose,
    required this.onSend,
  });

  final bool isLoading;
  final List<Map<String, dynamic>> messages;
  final TextEditingController controller;
  final ScrollController scrollController;
  final VoidCallback onClose;
  final VoidCallback onSend;

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.sizeOf(context);
    final width = size.width < 430 ? size.width - 28 : 390.0;
    final height = size.height < 720 ? size.height - 132 : 560.0;

    return Material(
      color: Colors.transparent,
      child: Container(
        width: width,
        height: height,
        clipBehavior: Clip.antiAlias,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: const Color(0xFFDBE3EF)),
          boxShadow: const [
            BoxShadow(
              color: Color(0x330F172A),
              blurRadius: 40,
              offset: Offset(0, 18),
            ),
          ],
        ),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(14),
              color: const Color(0xFF0F172A),
              child: Row(
                children: [
                  const Icon(Icons.support_agent, color: Colors.white),
                  const SizedBox(width: 10),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'خدمة العملاء',
                          style: TextStyle(
                            color: Colors.white,
                            fontFamily: 'Arial',
                            fontSize: 15,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        Text(
                          'اكتب رسالتك وسيتم الرد عليك',
                          style: TextStyle(
                            color: Color(0xFFCBD5E1),
                            fontFamily: 'Arial',
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    onPressed: onClose,
                    icon: const Icon(Icons.close, color: Colors.white),
                  ),
                ],
              ),
            ),
            Expanded(
              child: isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : messages.isEmpty
                  ? const Center(
                      child: Padding(
                        padding: EdgeInsets.all(18),
                        child: Text(
                          'لا توجد رسائل بعد. ابدأ المحادثة الآن.',
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            color: Color(0xFF64748B),
                            fontFamily: 'Arial',
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                    )
                  : ListView.separated(
                      controller: scrollController,
                      padding: const EdgeInsets.all(14),
                      itemCount: messages.length,
                      separatorBuilder: (_, _) => const SizedBox(height: 10),
                      itemBuilder: (context, index) {
                        final message = messages[index];
                        return _MessageBubble(message: message);
                      },
                    ),
            ),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: const BoxDecoration(
                border: Border(top: BorderSide(color: Color(0xFFE5E7EB))),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: controller,
                      minLines: 1,
                      maxLines: 3,
                      decoration: InputDecoration(
                        hintText: 'اكتب رسالتك هنا...',
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 10,
                        ),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(
                            color: Color(0xFFCBD5E1),
                          ),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton(
                    onPressed: onSend,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF16A34A),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 14,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: const Text('إرسال'),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _MessageBubble extends StatelessWidget {
  const _MessageBubble({required this.message});

  final Map<String, dynamic> message;

  @override
  Widget build(BuildContext context) {
    final isMine = message['is_mine'] == true;

    return Align(
      alignment: isMine ? Alignment.centerLeft : Alignment.centerRight,
      child: Container(
        constraints: const BoxConstraints(maxWidth: 300),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
        decoration: BoxDecoration(
          color: isMine ? const Color(0xFF0F4C81) : Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: isMine ? const Color(0xFF0F4C81) : const Color(0xFFE2E8F0),
          ),
          boxShadow: const [
            BoxShadow(
              color: Color(0x120F172A),
              blurRadius: 14,
              offset: Offset(0, 8),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              message['sender_name']?.toString() ?? 'مستخدم',
              style: TextStyle(
                color: isMine ? Colors.white70 : const Color(0xFF64748B),
                fontFamily: 'Arial',
                fontSize: 10,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              message['message']?.toString() ?? '',
              style: TextStyle(
                color: isMine ? Colors.white : const Color(0xFF0F172A),
                fontFamily: 'Arial',
                fontSize: 13,
                height: 1.6,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
