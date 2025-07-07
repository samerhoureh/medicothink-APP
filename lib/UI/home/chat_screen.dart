import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../models/conversation.dart';
import '../../services/conversation_service.dart';
import '../../services/image_service.dart';
import 'chat_drawer.dart';
import 'image_picker_bottom_sheet.dart';

const Color kTeal = Color(0xFF20A9C3);

class ChatScreen extends StatefulWidget {
  final String? conversationId;
  
  const ChatScreen({super.key, this.conversationId});

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  final TextEditingController _textCtrl = TextEditingController();
  final ScrollController _scrollCtrl = ScrollController();

  Conversation? _currentConversation;
  bool _isTyping = false;
  bool _isAnalyzingImage = false;

  @override
  void initState() {
    super.initState();
    _loadConversation();
  }

  Future<void> _loadConversation() async {
    if (widget.conversationId != null) {
      final conversations = await ConversationService.getConversations();
      final conversation = conversations.firstWhere(
        (c) => c.id == widget.conversationId,
        orElse: () => _createNewConversation(),
      );
      setState(() {
        _currentConversation = conversation;
      });
      await ConversationService.setCurrentConversationId(conversation.id);
    } else {
      final currentId = await ConversationService.getCurrentConversationId();
      if (currentId != null) {
        final conversations = await ConversationService.getConversations();
        final conversation = conversations.firstWhere(
          (c) => c.id == currentId,
          orElse: () => _createNewConversation(),
        );
        setState(() {
          _currentConversation = conversation;
        });
      } else {
        setState(() {
          _currentConversation = _createNewConversation();
        });
      }
    }
    _scrollToBottom();
  }

  Conversation _createNewConversation() {
    final now = DateTime.now();
    return Conversation(
      id: 'conv_${now.millisecondsSinceEpoch}',
      title: 'New Conversation',
      messages: [
        Message(
          id: 'msg_${now.millisecondsSinceEpoch}',
          text: 'Hello! I\'m your medical assistant. How can I help you today?',
          isMe: false,
          timestamp: now,
        ),
      ],
      createdAt: now,
      lastMessageAt: now,
    );
  }

  void _scrollToBottom() => WidgetsBinding.instance.addPostFrameCallback((_) {
    if (_scrollCtrl.hasClients) {
      _scrollCtrl.animateTo(
        _scrollCtrl.position.maxScrollExtent,
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeOut,
      );
    }
  });

  Future<void> _sendMessage({String? text, String? imagePath}) async {
    if (_currentConversation == null) return;
    
    final messageText = text ?? _textCtrl.text.trim();
    if (messageText.isEmpty && imagePath == null) return;

    final now = DateTime.now();
    final userMessage = Message(
      id: 'msg_${now.millisecondsSinceEpoch}',
      text: imagePath != null ? 'Sent an image' : messageText,
      isMe: true,
      timestamp: now,
      type: imagePath != null ? MessageType.image : MessageType.text,
      imagePath: imagePath,
    );

    setState(() {
      _currentConversation = _currentConversation!.copyWith(
        messages: [..._currentConversation!.messages, userMessage],
        lastMessageAt: now,
        title: _currentConversation!.title == 'New Conversation' 
            ? ConversationService.generateConversationTitle(messageText)
            : _currentConversation!.title,
      );
      if (imagePath != null) {
        _isAnalyzingImage = true;
      } else {
        _isTyping = true;
      }
    });

    _textCtrl.clear();
    _scrollToBottom();

    // Save conversation
    await ConversationService.saveConversation(_currentConversation!);
    await ConversationService.setCurrentConversationId(_currentConversation!.id);

    // Generate response
    String responseText;
    if (imagePath != null) {
      responseText = await ImageService.analyzeImage(imagePath);
    } else {
      await Future.delayed(const Duration(milliseconds: 1000));
      responseText = _generateResponse(messageText);
    }

    final responseMessage = Message(
      id: 'msg_${DateTime.now().millisecondsSinceEpoch}',
      text: responseText,
      isMe: false,
      timestamp: DateTime.now(),
    );

    setState(() {
      _isTyping = false;
      _isAnalyzingImage = false;
      _currentConversation = _currentConversation!.copyWith(
        messages: [..._currentConversation!.messages, responseMessage],
        lastMessageAt: DateTime.now(),
      );
    });

    _scrollToBottom();
    await ConversationService.saveConversation(_currentConversation!);
  }

  String _generateResponse(String message) {
    final responses = [
      'Thank you for sharing that information. Based on what you\'ve described, I recommend consulting with a healthcare professional for proper evaluation.',
      'I understand your concern. It\'s important to monitor your symptoms and seek medical attention if they persist or worsen.',
      'That\'s a good question. While I can provide general information, it\'s always best to discuss specific symptoms with your doctor.',
      'I appreciate you reaching out. For personalized medical advice, please consult with a qualified healthcare provider.',
    ];
    return responses[DateTime.now().millisecond % responses.length];
  }

  Future<void> _showImagePicker() async {
    final result = await ImagePickerBottomSheet.show(context);

    if (result != null) {
      final isValid = await ImageService.isValidImageFile(result);
      if (isValid) {
        await _sendMessage(imagePath: result);
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Please select a valid image file'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    }
  }

  Future<void> _archiveConversation() async {
    if (_currentConversation == null) return;
    
    await ConversationService.archiveConversation(_currentConversation!.id);
    
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Conversation archived'),
          backgroundColor: kTeal,
        ),
      );
      
      // Create new conversation
      setState(() {
        _currentConversation = _createNewConversation();
      });
      await ConversationService.saveConversation(_currentConversation!);
      await ConversationService.setCurrentConversationId(_currentConversation!.id);
    }
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => FocusScope.of(context).unfocus(),
      child: Scaffold(
        key: _scaffoldKey,
        backgroundColor: Colors.white,
        drawer: ChatDrawer(
          currentConversationId: _currentConversation?.id,
          onConversationSelected: (conversationId) {
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(
                builder: (context) => ChatScreen(conversationId: conversationId),
              ),
            );
          },
        ),
        onDrawerChanged: (isOpen) {
          if (!isOpen) FocusScope.of(context).unfocus();
        },
        appBar: AppBar(
          scrolledUnderElevation: 0,
          surfaceTintColor: Colors.transparent,
          leadingWidth: 90,
          toolbarHeight: 80,
          backgroundColor: Colors.white,
          elevation: 0,
          leading: Builder(
            builder: (context) => Padding(
              padding: const EdgeInsets.only(
                left: 30,
                top: 20,
                bottom: 20,
                right: 20,
              ),
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.grey.withOpacity(0.3),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: IconButton(
                  icon: const Icon(Icons.menu, color: kTeal),
                  onPressed: () {
                    FocusScope.of(context).unfocus();
                    Scaffold.of(context).openDrawer();
                  },
                ),
              ),
            ),
          ),
          title: Text(
            _currentConversation?.title ?? 'Medical Assistant',
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w600,
            ),
          ),
          actions: [
            PopupMenuButton<String>(
              icon: const Icon(Icons.more_vert),
              onSelected: (value) {
                switch (value) {
                  case 'archive':
                    _archiveConversation();
                    break;
                }
              },
              itemBuilder: (context) => [
                const PopupMenuItem(
                  value: 'archive',
                  child: Row(
                    children: [
                      Icon(Icons.archive_outlined),
                      SizedBox(width: 8),
                      Text('Archive Conversation'),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(width: 8),
          ],
        ),
        body: Column(
          children: [
            Expanded(
              child: ListView.builder(
                controller: _scrollCtrl,
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 8,
                ),
                itemCount: (_currentConversation?.messages.length ?? 0) + 
                    (_isTyping || _isAnalyzingImage ? 1 : 0) + 1,
                itemBuilder: (_, i) {
                  if (i == 0) return const _DateLabel(label: 'Today');

                  int index = i - 1;
                  final messages = _currentConversation?.messages ?? [];

                  if (index < messages.length) {
                    final msg = messages[index];
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        _ChatBubble(message: msg),
                        if (msg.isMe) const _SeenLabel(),
                      ],
                    );
                  }

                  index -= messages.length;

                  if ((_isTyping || _isAnalyzingImage) && index == 0) {
                    return _TypingLabel(
                      name: 'Medical Assistant',
                      isAnalyzing: _isAnalyzingImage,
                    );
                  }

                  return const SizedBox.shrink();
                },
              ),
            ),
            _MessageInput(
              controller: _textCtrl,
              onSend: () => _sendMessage(),
              onImagePick: _showImagePicker,
            ),
          ],
        ),
      ),
    );
  }
}

class _ChatBubble extends StatelessWidget {
  final Message message;
  
  const _ChatBubble({required this.message});

  @override
  Widget build(BuildContext context) {
    final bg = message.isMe ? kTeal : const Color.fromARGB(255, 227, 242, 241);
    final align = message.isMe ? Alignment.centerRight : Alignment.centerLeft;
    final txtColor = message.isMe ? Colors.white : Colors.black87;

    return Align(
      alignment: align,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 4),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.75,
        ),
        decoration: BoxDecoration(
          color: bg,
          borderRadius: BorderRadius.only(
            topLeft: const Radius.circular(12),
            topRight: const Radius.circular(12),
            bottomLeft: Radius.circular(message.isMe ? 12 : 0),
            bottomRight: Radius.circular(message.isMe ? 0 : 12),
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (message.type == MessageType.image && message.imagePath != null)
              Container(
                margin: const EdgeInsets.only(bottom: 8),
                height: 200,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(8),
                  image: DecorationImage(
                    image: FileImage(File(message.imagePath!)),
                    fit: BoxFit.cover,
                  ),
                ),
              ),
            Text(
              message.text,
              style: TextStyle(color: txtColor),
            ),
          ],
        ),
      ),
    );
  }
}

class _DateLabel extends StatelessWidget {
  final String label;
  const _DateLabel({required this.label});
  
  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Text(
        label,
        style: const TextStyle(color: Color.fromARGB(255, 158, 158, 158)),
      ),
    ),
  );
}

class _SeenLabel extends StatelessWidget {
  const _SeenLabel();
  
  @override
  Widget build(BuildContext context) => const Padding(
    padding: EdgeInsets.only(right: 8.0, top: 2, bottom: 8),
    child: Align(
      alignment: Alignment.centerRight,
      child: Text('Seen', style: TextStyle(color: Colors.grey, fontSize: 12)),
    ),
  );
}

class _TypingLabel extends StatelessWidget {
  final String name;
  final bool isAnalyzing;
  
  const _TypingLabel({required this.name, this.isAnalyzing = false});
  
  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 6.0, horizontal: 8),
    child: Align(
      alignment: Alignment.centerLeft,
      child: Text(
        isAnalyzing ? '$name is analyzing image...' : '$name is typing...',
        style: const TextStyle(color: Colors.grey, fontSize: 12),
      ),
    ),
  );
}

class _MessageInput extends StatelessWidget {
  final TextEditingController controller;
  final VoidCallback onSend;
  final VoidCallback onImagePick;
  
  const _MessageInput({
    required this.controller,
    required this.onSend,
    required this.onImagePick,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.transparent,
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
      child: Row(
        children: [
          Expanded(
            child: TextField(
              controller: controller,
              decoration: InputDecoration(
                hintText: 'Type something...',
                hintStyle: const TextStyle(color: Color.fromARGB(255, 209, 209, 209)),
                prefixIcon: IconButton(
                  icon: const Icon(
                    Icons.image_outlined,
                    color: Color.fromARGB(255, 209, 209, 209),
                  ),
                  onPressed: onImagePick,
                ),
                filled: true,
                fillColor: const Color(0xFFF0F0F0),
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 12,
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: BorderSide.none,
                ),
              ),
              onSubmitted: (_) => onSend(),
            ),
          ),
          const SizedBox(width: 12),
          ValueListenableBuilder<TextEditingValue>(
            valueListenable: controller,
            builder: (_, value, __) {
              final hasText = value.text.trim().isNotEmpty;
              return SizedBox(
                width: 50,
                height: 50,
                child: FloatingActionButton(
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(50),
                  ),
                  backgroundColor: Colors.black,
                  onPressed: hasText ? onSend : onImagePick,
                  child: Icon(
                    hasText ? Icons.send : Icons.add,
                    size: 28,
                    color: Colors.white,
                  ),
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}