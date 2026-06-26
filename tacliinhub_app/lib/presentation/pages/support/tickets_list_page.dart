import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/support_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../data/models/support_models.dart';
import 'create_ticket_page.dart';
import 'ticket_detail_page.dart';

class TicketsListPage extends StatefulWidget {
  const TicketsListPage({super.key});

  @override
  State<TicketsListPage> createState() => _TicketsListPageState();
}

class _TicketsListPageState extends State<TicketsListPage> {
  String? _selectedStatus;
  String? _selectedPriority;
  String? _selectedCategory;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadTickets();
    });
  }

  void _loadTickets() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    final provider = context.read<SupportProvider>();
    provider.loadTickets(
      userId: user?.id,
      status: _selectedStatus,
      priority: _selectedPriority,
      category: _selectedCategory,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'My Tickets',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.deepPurple,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const CreateTicketPage()),
            ),
            tooltip: 'Create Ticket',
          ),
        ],
      ),
      body: Column(
        children: [
          // Filters
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              children: [
                DropdownButtonFormField<String>(
                  decoration: InputDecoration(
                    labelText: 'Status',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 8,
                    ),
                  ),
                  initialValue: _selectedStatus,
                  items: const [
                    DropdownMenuItem<String>(
                      value: null,
                      child: Text('All Status'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Open',
                      child: Text('Open'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'In Progress',
                      child: Text('In Progress'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Resolved',
                      child: Text('Resolved'),
                    ),
                    DropdownMenuItem<String>(
                      value: 'Closed',
                      child: Text('Closed'),
                    ),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedStatus = value;
                    });
                    _loadTickets();
                  },
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        decoration: InputDecoration(
                          labelText: 'Priority',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          contentPadding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 8,
                          ),
                        ),
                        initialValue: _selectedPriority,
                        items: const [
                          DropdownMenuItem<String>(
                            value: null,
                            child: Text('All Priorities'),
                          ),
                          DropdownMenuItem<String>(
                            value: 'Critical',
                            child: Text('Critical'),
                          ),
                          DropdownMenuItem<String>(
                            value: 'High',
                            child: Text('High'),
                          ),
                          DropdownMenuItem<String>(
                            value: 'Medium',
                            child: Text('Medium'),
                          ),
                          DropdownMenuItem<String>(
                            value: 'Low',
                            child: Text('Low'),
                          ),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _selectedPriority = value;
                          });
                          _loadTickets();
                        },
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        decoration: InputDecoration(
                          labelText: 'Category',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          contentPadding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 8,
                          ),
                        ),
                        initialValue: _selectedCategory,
                        items: const [
                          DropdownMenuItem<String>(
                            value: null,
                            child: Text('All Categories'),
                          ),
                          DropdownMenuItem<String>(
                            value: 'General',
                            child: Text('General'),
                          ),
                          DropdownMenuItem<String>(
                            value: 'Technical',
                            child: Text('Technical'),
                          ),
                          DropdownMenuItem<String>(
                            value: 'Billing',
                            child: Text('Billing'),
                          ),
                          DropdownMenuItem<String>(
                            value: 'Feature Request',
                            child: Text('Feature Request'),
                          ),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _selectedCategory = value;
                          });
                          _loadTickets();
                        },
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          // Tickets List
          Expanded(
            child: Consumer<SupportProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading && provider.tickets.isEmpty) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (provider.error != null && provider.tickets.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.error_outline, size: 48, color: Colors.red),
                        const SizedBox(height: 16),
                        Text(provider.error ?? 'Error loading tickets'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadTickets,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                if (provider.tickets.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.support_agent_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No tickets found',
                          style: GoogleFonts.montserrat(
                            fontSize: 16,
                            color: Colors.grey[600],
                          ),
                        ),
                        const SizedBox(height: 24),
                        ElevatedButton.icon(
                          onPressed: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => const CreateTicketPage(),
                            ),
                          ),
                          icon: const Icon(Icons.add),
                          label: const Text('Create Ticket'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.deepPurple,
                            padding: const EdgeInsets.symmetric(
                              horizontal: 24,
                              vertical: 12,
                            ),
                          ),
                        ),
                      ],
                    ),
                  );
                }

                return RefreshIndicator(
                  onRefresh: () async => _loadTickets(),
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: provider.tickets.length,
                    itemBuilder: (context, index) {
                      final ticket = provider.tickets[index];
                      return _buildTicketCard(context, ticket);
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const CreateTicketPage()),
        ),
        backgroundColor: Colors.deepPurple,
        child: const Icon(Icons.add),
      ),
    );
  }

  Widget _buildTicketCard(BuildContext context, SupportTicket ticket) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => TicketDetailPage(ticketId: ticket.id),
          ),
        ),
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          ticket.ticketNo,
                          style: GoogleFonts.montserrat(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: Colors.deepPurple,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          ticket.subject,
                          style: GoogleFonts.montserrat(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: ticket.priorityColor.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          ticket.priority,
                          style: GoogleFonts.montserrat(
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                            color: ticket.priorityColor,
                          ),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: ticket.statusColor.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          ticket.status,
                          style: GoogleFonts.montserrat(
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                            color: ticket.statusColor,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                ticket.description,
                style: GoogleFonts.montserrat(
                  fontSize: 14,
                  color: Colors.grey[700],
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  if (ticket.category != null) ...[
                    Icon(Icons.category, size: 14, color: Colors.grey[600]),
                    const SizedBox(width: 4),
                    Text(
                      ticket.category!,
                      style: GoogleFonts.montserrat(
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                    ),
                    const SizedBox(width: 16),
                  ],
                  Icon(
                    Icons.chat_bubble_outline,
                    size: 14,
                    color: Colors.grey[600],
                  ),
                  const SizedBox(width: 4),
                  Text(
                    '${ticket.replyCount} replies',
                    style: GoogleFonts.montserrat(
                      fontSize: 12,
                      color: Colors.grey[600],
                    ),
                  ),
                  const Spacer(),
                  Text(
                    DateFormat(
                      'MMM d, yyyy',
                    ).format(DateTime.parse(ticket.createdAt)),
                    style: GoogleFonts.montserrat(
                      fontSize: 12,
                      color: Colors.grey[600],
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
