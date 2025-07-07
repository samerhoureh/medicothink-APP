import 'package:flutter/material.dart';
import '../../models/conversation.dart';
import '../../services/conversation_service.dart';
import 'archived_conversations_screen.dart';

const Color kTeal = Color(0xFF20A9C3);
const Color kIndicatorGrey = Color(0xFFD9D9D9);

class ChatDrawer extends StatefulWidget {
  final String? currentConversationId;
  final Function(String) onConversationSelected;

  const ChatDrawer({
    super.key,
    this.currentConversationId,
    required this.onConversationSelected,
  });

  @override
  State<ChatDrawer> createState() => _ChatDrawerState();
}

class _ChatDrawerState extends State<ChatDrawer> {
  List<Conversation> _conversations = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadConversations();
  }

  Future<void> _loadConversations() async {
    final conversations = await ConversationService.getActiveConversations();
    setState(() {
      _conversations = conversations;
      _isLoading = false;
    });
  }

  Future<void> _deleteConversation(String conversationId) async {
    await ConversationService.deleteConversation(conversationId);
    await _loadConversations();
  }

  Future<void> _archiveConversation(String conversationId) async {
    await ConversationService.archiveConversation(conversationId);
    await _loadConversations();
    
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Conversation archived'),
          backgroundColor: kTeal,
        ),
      );
    }
  }

  String _formatDate(DateTime date) {
    final now = DateTime.now();
    final difference = now.difference(date).inDays;
    
    if (difference == 0) {
      return 'Today';
    } else if (difference == 1) {
      return 'Yesterday';
    } else if (difference <= 7) {
      return 'Previous 7 Days';
    } else {
      return 'Older';
    }
  }

  Map<String, List<Conversation>> _groupConversationsByDate() {
    final grouped = <String, List<Conversation>>{};
    
    for (final conversation in _conversations) {
      final dateKey = _formatDate(conversation.lastMessageAt);
      grouped.putIfAbsent(dateKey, () => []).add(conversation);
    }
    
    return grouped;
  }

  @override
  Widget build(BuildContext context) {
    return Drawer(
      width: 270,
      backgroundColor: Colors.white,
      child: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 10),
            Center(
              child: Image.asset(
                'assets/images/splash/medico_logo.jpg',
                height: 120,
                width: 120,
              ),
            ),
            const SizedBox(height: 20),
            
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0),
              child: Row(
                children: [
                  const Expanded(
                    child: Text(
                      'Chat History',
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 18,
                      ),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.archive_outlined, size: 20),
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => const ArchivedConversationsScreen(),
                        ),
                      );
                    },
                    tooltip: 'View Archived',
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            if (_isLoading)
              const Expanded(
                child: Center(
                  child: CircularProgressIndicator(color: kTeal),
                ),
              )
            else if (_conversations.isEmpty)
              const Expanded(
                child: Center(
                  child: Text(
                    'No conversations yet',
                    style: TextStyle(
                      color: Colors.grey,
                      fontSize: 14,
                    ),
                  ),
                ),
              )
            else
              Expanded(
                child: ListView(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  children: [
                    for (final entry in _groupConversationsByDate().entries)
                      _DrawerSection(
                        label: entry.key,
                        conversations: entry.value,
                        currentConversationId: widget.currentConversationId,
                        onConversationSelected: widget.onConversationSelected,
                        onConversationDeleted: _deleteConversation,
                        onConversationArchived: _archiveConversation,
                      ),
                  ],
                ),
              ),
            
            // User Profile Section
            Container(
              margin: const EdgeInsets.all(16),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: const Color(0xFFF8F9FA),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: Colors.grey.withOpacity(0.2),
                  width: 1,
                ),
              ),
              child: Row(
                children: [
                  // Profile Image
                  Container(
                    width: 50,
                    height: 50,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(25),
                      image: const DecorationImage(
                        image: AssetImage('assets/images/splash/medico_logo.jpg'),
                        fit: BoxFit.cover,
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  // User Info
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Dr. Sarah',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                            color: Colors.black87,
                          ),
                        ),
                        const SizedBox(height: 2),
                        const Text(
                          'Cardiologist',
                          style: TextStyle(
                            fontSize: 13,
                            color: Colors.grey,
                          ),
                        ),

                      ],
                    ),
                  ),
                  // Settings Icon
                  IconButton(
                    icon: const Icon(
                      Icons.settings_outlined,
                      color: kTeal,
                      size: 20,
                    ),
                    onPressed: () {
                      Navigator.pushNamed(context, '/profile-settings');
                    },
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

class _DrawerSection extends StatelessWidget {
  final String label;
  final List<Conversation> conversations;
  final String? currentConversationId;
  final Function(String) onConversationSelected;
  final Function(String) onConversationDeleted;
  final Function(String) onConversationArchived;

  const _DrawerSection({
    required this.label,
    required this.conversations,
    this.currentConversationId,
    required this.onConversationSelected,
    required this.onConversationDeleted,
    required this.onConversationArchived,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            color: label == 'Today' ? Colors.blue : Colors.grey,
            fontSize: 13,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 8),

        for (final conversation in conversations)
          InkWell(
            onTap: () {
              Navigator.pop(context);
              onConversationSelected(conversation.id);
            },
            onLongPress: () {
              _showConversationOptions(context, conversation);
            },
            child: Container(
              width: double.infinity,
              decoration: BoxDecoration(
                color: (currentConversationId == conversation.id) 
                    ? kIndicatorGrey 
                    : Colors.transparent,
                borderRadius: BorderRadius.circular(6),
              ),
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 8),
              child: Text(
                conversation.title,
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.black87,
                  fontWeight: (currentConversationId == conversation.id) 
                      ? FontWeight.w600 
                      : null,
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ),

        const SizedBox(height: 20),
      ],
    );
  }

  void _showConversationOptions(BuildContext context, Conversation conversation) {
    showModalBottomSheet(
      context: context,
      builder: (context) => Container(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.archive_outlined),
              title: const Text('Archive'),
              onTap: () {
                Navigator.pop(context);
                onConversationArchived(conversation.id);
              },
            ),
            ListTile(
              leading: const Icon(Icons.delete_outline, color: Colors.red),
              title: const Text('Delete', style: TextStyle(color: Colors.red)),
              onTap: () {
                Navigator.pop(context);
                _showDeleteConfirmation(context, conversation);
              },
            ),
          ],
        ),
      ),
    );
  }

  void _showDeleteConfirmation(BuildContext context, Conversation conversation) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Conversation'),
        content: const Text('Are you sure you want to delete this conversation? This action cannot be undone.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              onConversationDeleted(conversation.id);
            },
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }
}