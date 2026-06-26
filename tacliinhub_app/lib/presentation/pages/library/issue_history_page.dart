import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/library_provider.dart';
import '../../providers/auth_provider.dart';

class IssueHistoryPage extends StatefulWidget {
  const IssueHistoryPage({super.key});

  @override
  State<IssueHistoryPage> createState() => _IssueHistoryPageState();
}

class _IssueHistoryPageState extends State<IssueHistoryPage> {
  String? _selectedStatus;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      context.read<LibraryProvider>().loadIssues(userId: user?.id);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Issue History',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.purple,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Filter
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: DropdownButtonFormField<String>(
              decoration: InputDecoration(
                labelText: 'Filter by Status',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                prefixIcon: const Icon(Icons.filter_list),
              ),
              initialValue: _selectedStatus,
              items: const [
                DropdownMenuItem<String>(
                  value: null,
                  child: Text('All Statuses'),
                ),
                DropdownMenuItem(value: 'Issued', child: Text('Issued')),
                DropdownMenuItem(value: 'Returned', child: Text('Returned')),
                DropdownMenuItem(value: 'Overdue', child: Text('Overdue')),
                DropdownMenuItem(value: 'Lost', child: Text('Lost')),
              ],
              onChanged: (value) {
                setState(() {
                  _selectedStatus = value;
                });
                _loadIssues();
              },
            ),
          ),

          // Issues List
          Expanded(
            child: Consumer<LibraryProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (provider.error != null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.error_outline,
                          size: 48,
                          color: Colors.red,
                        ),
                        const SizedBox(height: 16),
                        Text(provider.error ?? 'Error loading issue history'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadIssues,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                var issues = provider.issues;
                if (_selectedStatus != null) {
                  issues = issues
                      .where((issue) => issue.status == _selectedStatus)
                      .toList();
                }

                if (issues.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.history_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No issue history found',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  );
                }

                return ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: issues.length,
                  itemBuilder: (context, index) {
                    final issue = issues[index];
                    final isOverdue =
                        issue.status == 'Overdue' ||
                        (issue.dueDate != null &&
                            DateTime.parse(
                              issue.dueDate!,
                            ).isBefore(DateTime.now()) &&
                            issue.status == 'Issued');

                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                        side: BorderSide(
                          color: isOverdue
                              ? Colors.red
                              : issue.status == 'Returned'
                              ? Colors.green
                              : Colors.transparent,
                          width: isOverdue || issue.status == 'Returned'
                              ? 2
                              : 0,
                        ),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: _getStatusColor(
                            issue.status,
                          ).withOpacity(0.1),
                          child: Icon(
                            _getStatusIcon(issue.status),
                            color: _getStatusColor(issue.status),
                          ),
                        ),
                        title: Text(
                          issue.bookTitle,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text('${issue.memberName} (${issue.memberType})'),
                            Text(
                              'Issued: ${DateFormat('MMM d, yyyy').format(DateTime.parse(issue.issueDate))}',
                              style: GoogleFonts.montserrat(fontSize: 12),
                            ),
                            if (issue.dueDate != null)
                              Text(
                                'Due: ${DateFormat('MMM d, yyyy').format(DateTime.parse(issue.dueDate!))}',
                                style: GoogleFonts.montserrat(
                                  fontSize: 12,
                                  color: isOverdue
                                      ? Colors.red
                                      : Colors.grey[600],
                                  fontWeight: isOverdue
                                      ? FontWeight.bold
                                      : FontWeight.normal,
                                ),
                              ),
                            if (issue.returnDate != null)
                              Text(
                                'Returned: ${DateFormat('MMM d, yyyy').format(DateTime.parse(issue.returnDate!))}',
                                style: GoogleFonts.montserrat(fontSize: 12),
                              ),
                            if (issue.fineAmount != null &&
                                issue.fineAmount! > 0)
                              Text(
                                'Fine: \$${issue.fineAmount!.toStringAsFixed(2)}',
                                style: GoogleFonts.montserrat(
                                  fontSize: 12,
                                  color: Colors.red,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                          ],
                        ),
                        trailing: Chip(
                          label: Text(issue.status),
                          backgroundColor: _getStatusColor(
                            issue.status,
                          ).withOpacity(0.1),
                          labelStyle: TextStyle(
                            color: _getStatusColor(issue.status),
                            fontSize: 10,
                          ),
                        ),
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  void _loadIssues() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    context.read<LibraryProvider>().loadIssues(
      status: _selectedStatus,
      userId: user?.id,
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'returned':
        return Colors.green;
      case 'overdue':
        return Colors.red;
      case 'lost':
        return Colors.grey;
      default:
        return Colors.blue;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status.toLowerCase()) {
      case 'returned':
        return Icons.check_circle;
      case 'overdue':
        return Icons.warning;
      case 'lost':
        return Icons.block;
      default:
        return Icons.book;
    }
  }
}
