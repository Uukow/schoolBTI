import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/communication_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class MessagesPage extends StatefulWidget {
  const MessagesPage({super.key});

  @override
  State<MessagesPage> createState() => _MessagesPageState();
}

class _MessagesPageState extends State<MessagesPage> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  String? _selectedMessageType;
  bool _isInitialLoad = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _tabController.addListener(() {
      setState(() {
        _selectedMessageType = _tabController.index == 0
            ? 'Inbox'
            : _tabController.index == 1
                ? 'Sent'
                : 'Draft';
        _isInitialLoad = true;
      });
      _loadMessages();
    });
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadMessages();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadMessages() async {
    try {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user?.id == null) {
        if (mounted) {
          SweetAlert.showError(
            context: context,
            title: 'Authentication Error',
            message: 'Please login to view messages',
          );
        }
        return;
      }

      final provider = context.read<CommunicationProvider>();
      await provider.loadMessages(
        userId: user?.id,
        messageType: _selectedMessageType,
      );

      if (mounted) {
        setState(() {
          _isInitialLoad = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isInitialLoad = false;
        });
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: 'Failed to load messages: ${e.toString()}',
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Messages',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.blue,
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          tabs: const [
            Tab(icon: Icon(Icons.inbox), text: 'Inbox'),
            Tab(icon: Icon(Icons.send), text: 'Sent'),
            Tab(icon: Icon(Icons.drafts), text: 'Draft'),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              setState(() {
                _isInitialLoad = true;
              });
              _loadMessages();
            },
            tooltip: 'Refresh',
          ),
          IconButton(
            icon: const Icon(Icons.send),
            onPressed: () async {
              final result = await Navigator.pushNamed(
                context,
                '/communication/messages/send',
              );
              if (result == true && mounted) {
                _loadMessages();
              }
            },
            tooltip: 'Send Message',
          ),
        ],
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildMessagesList('Inbox'),
          _buildMessagesList('Sent'),
          _buildMessagesList('Draft'),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          final result = await Navigator.pushNamed(
            context,
            '/communication/messages/send',
          );
          if (result == true && mounted) {
            _loadMessages();
          }
        },
        backgroundColor: Colors.blue,
        icon: const Icon(Icons.send),
        label: Text(
          'Send',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.w600),
        ),
      ),
    );
  }

  Widget _buildMessagesList(String type) {
    return Consumer<CommunicationProvider>(
      builder: (context, provider, child) {
        // Initial loading state
        if (_isInitialLoad && provider.isLoading) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const CircularProgressIndicator(),
                const SizedBox(height: 16),
                Text(
                  'Loading messages...',
                  style: GoogleFonts.montserrat(
                    color: Colors.grey[600],
                  ),
                ),
              ],
            ),
          );
        }

        // Error state
        if (provider.error != null && !provider.isLoading) {
          return _buildErrorState(provider.error!, _loadMessages);
        }

        final messages = provider.messages.where((m) => m.messageType == type).toList();

        // Empty state
        if (messages.isEmpty && !provider.isLoading) {
          return _buildEmptyState(type);
        }

        // Success state with data
        return RefreshIndicator(
          onRefresh: _loadMessages,
          child: ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: messages.length,
            itemBuilder: (context, index) {
              final message = messages[index];
              return _buildMessageCard(message, type);
            },
          ),
        );
      },
    );
  }

  Widget _buildErrorState(String error, VoidCallback onRetry) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.red.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(Icons.error_outline, size: 64, color: Colors.red[700]),
            ),
            const SizedBox(height: 24),
            Text(
              'Failed to Load Messages',
              style: GoogleFonts.montserrat(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.grey[800],
              ),
            ),
            const SizedBox(height: 12),
            Text(
              error,
              textAlign: TextAlign.center,
              style: GoogleFonts.montserrat(
                fontSize: 14,
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh),
              label: const Text('Retry'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.blue,
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 12,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState(String type) {
    final icon = type == 'Inbox'
        ? Icons.inbox_outlined
        : type == 'Sent'
            ? Icons.send_outlined
            : Icons.drafts_outlined;
    final message = type == 'Inbox'
        ? 'No messages in your inbox'
        : type == 'Sent'
            ? 'No sent messages'
            : 'No draft messages';

    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.blue.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, size: 64, color: Colors.blue[700]),
            ),
            const SizedBox(height: 24),
            Text(
              message,
              style: GoogleFonts.montserrat(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.grey[800],
              ),
            ),
            const SizedBox(height: 12),
            Text(
              type == 'Inbox'
                  ? 'Messages you receive will appear here'
                  : type == 'Sent'
                      ? 'Messages you send will appear here'
                      : 'Draft messages will appear here',
              textAlign: TextAlign.center,
              style: GoogleFonts.montserrat(
                fontSize: 14,
                color: Colors.grey[600],
              ),
            ),
            if (type == 'Inbox' || type == 'Sent') ...[
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: () async {
                  final result = await Navigator.pushNamed(
                    context,
                    '/communication/messages/send',
                  );
                  if (result == true && mounted) {
                    _loadMessages();
                  }
                },
                icon: const Icon(Icons.send),
                label: const Text('Send Message'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.blue,
                  padding: const EdgeInsets.symmetric(
                    horizontal: 24,
                    vertical: 12,
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildMessageCard(message, String type) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.all(16),
        leading: CircleAvatar(
          backgroundColor: message.isRead
              ? Colors.grey.withOpacity(0.1)
              : Colors.blue.withOpacity(0.1),
          child: Icon(
            message.isRead ? Icons.mark_email_read : Icons.mark_email_unread,
            color: message.isRead ? Colors.grey : Colors.blue,
          ),
        ),
        title: Text(
          message.subject,
          style: GoogleFonts.montserrat(
            fontWeight: message.isRead ? FontWeight.normal : FontWeight.w600,
          ),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text(
              type == 'Inbox'
                  ? 'From: ${message.fromUserName ?? 'Unknown'}'
                  : 'To: ${message.toUserName ?? 'Unknown'}',
            ),
            const SizedBox(height: 4),
            Text(
              message.message.length > 100
                  ? '${message.message.substring(0, 100)}...'
                  : message.message,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 4),
            Text(
              DateFormat('MMM d, yyyy h:mm a').format(DateTime.parse(message.createdAt)),
              style: TextStyle(
                fontSize: 12,
                color: Colors.grey[600],
              ),
            ),
          ],
        ),
        trailing: message.attachmentUrl != null
            ? Icon(Icons.attach_file, color: Colors.blue)
            : null,
        onTap: () async {
          if (!message.isRead && type == 'Inbox') {
            try {
              final user = Provider.of<AuthProvider>(context, listen: false).user;
              final commProvider = Provider.of<CommunicationProvider>(context, listen: false);
              await commProvider.markMessageAsRead(message.id, userId: user?.id);
            } catch (e) {
              if (mounted) {
                SweetAlert.showError(
                  context: context,
                  title: 'Error',
                  message: 'Failed to mark message as read',
                );
              }
            }
          }
          _showMessageDetails(message, type);
        },
      ),
    );
  }

  void _showMessageDetails(message, String type) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
        ),
        title: Row(
          children: [
            Icon(Icons.message, color: Colors.blue),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                message.subject,
                style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.blue.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    Icon(Icons.person, size: 16, color: Colors.blue),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        type == 'Inbox'
                            ? 'From: ${message.fromUserName ?? 'Unknown'}'
                            : 'To: ${message.toUserName ?? 'Unknown'}',
                        style: GoogleFonts.montserrat(fontWeight: FontWeight.w600),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              Text(
                message.message,
                style: GoogleFonts.montserrat(),
              ),
              if (message.attachmentUrl != null) ...[
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.green.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.attach_file, color: Colors.green),
                      const SizedBox(width: 8),
                      Text(
                        'Attachment available',
                        style: TextStyle(color: Colors.green[700]),
                      ),
                    ],
                  ),
                ),
              ],
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }
}
