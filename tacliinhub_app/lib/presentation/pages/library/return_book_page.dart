import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/library_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class ReturnBookPage extends StatefulWidget {
  const ReturnBookPage({super.key});

  @override
  State<ReturnBookPage> createState() => _ReturnBookPageState();
}

class _ReturnBookPageState extends State<ReturnBookPage> {
  final _formKey = GlobalKey<FormState>();
  final _fineAmountController = TextEditingController(text: '0');
  final _remarksController = TextEditingController();
  int? _selectedIssueId;
  DateTime _returnDate = DateTime.now();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<LibraryProvider>();
      provider.loadIssues(status: 'Issued', userId: user?.id);
      // Also load books to ensure data is fresh
      provider.loadBooks(userId: user?.id);
    });
  }

  @override
  void dispose() {
    _fineAmountController.dispose();
    _remarksController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Return Book',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.orange,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Issue Selection
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Consumer<LibraryProvider>(
              builder: (context, provider, child) {
                final issuedBooks = provider.issuedBooks;
                return DropdownButtonFormField<int>(
                  decoration: InputDecoration(
                    labelText: 'Select Issued Book *',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    prefixIcon: const Icon(Icons.book),
                  ),
                  initialValue: _selectedIssueId,
                  items: issuedBooks.isEmpty
                      ? [
                          const DropdownMenuItem<int>(
                            value: null,
                            enabled: false,
                            child: Text('No issued books found'),
                          ),
                        ]
                      : issuedBooks.map((issue) {
                          final isOverdue =
                              issue.dueDate != null &&
                              DateTime.parse(
                                issue.dueDate!,
                              ).isBefore(DateTime.now());
                          return DropdownMenuItem<int>(
                            value: issue.id,
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  issue.bookTitle,
                                  style: GoogleFonts.montserrat(
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                Text(
                                  '${issue.memberName} | Due: ${issue.dueDate ?? "N/A"}',
                                  style: GoogleFonts.montserrat(
                                    fontSize: 12,
                                    color: isOverdue
                                        ? Colors.red
                                        : Colors.grey[600],
                                  ),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
                  onChanged: issuedBooks.isEmpty
                      ? null
                      : (value) {
                          setState(() {
                            _selectedIssueId = value;
                            // Calculate fine if overdue
                            if (value != null) {
                              final issue = issuedBooks.firstWhere(
                                (i) => i.id == value,
                              );
                              if (issue.dueDate != null) {
                                final dueDate = DateTime.parse(issue.dueDate!);
                                if (dueDate.isBefore(DateTime.now())) {
                                  final daysOverdue = DateTime.now()
                                      .difference(dueDate)
                                      .inDays;
                                  final fine = daysOverdue * 5.0; // $5 per day
                                  _fineAmountController.text = fine
                                      .toStringAsFixed(2);
                                } else {
                                  _fineAmountController.text = '0';
                                }
                              }
                            }
                          });
                        },
                );
              },
            ),
          ),

          // Return Form
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Card(
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Padding(
                        padding: const EdgeInsets.all(20),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Return Information',
                              style: GoogleFonts.montserrat(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 24),
                            InkWell(
                              onTap: () async {
                                final picked = await showDatePicker(
                                  context: context,
                                  initialDate: _returnDate,
                                  firstDate: DateTime.now().subtract(
                                    const Duration(days: 30),
                                  ),
                                  lastDate: DateTime.now(),
                                );
                                if (picked != null) {
                                  setState(() {
                                    _returnDate = picked;
                                  });
                                }
                              },
                              child: InputDecorator(
                                decoration: InputDecoration(
                                  labelText: 'Return Date *',
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  prefixIcon: const Icon(Icons.calendar_today),
                                ),
                                child: Text(
                                  DateFormat('yyyy-MM-dd').format(_returnDate),
                                ),
                              ),
                            ),
                            const SizedBox(height: 16),
                            TextFormField(
                              controller: _fineAmountController,
                              decoration: InputDecoration(
                                labelText: 'Fine Amount',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixText: '\$ ',
                                prefixIcon: const Icon(Icons.attach_money),
                              ),
                              keyboardType:
                                  const TextInputType.numberWithOptions(
                                    decimal: true,
                                  ),
                            ),
                            const SizedBox(height: 16),
                            TextFormField(
                              controller: _remarksController,
                              decoration: InputDecoration(
                                labelText: 'Remarks',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                              ),
                              maxLines: 3,
                            ),
                            const SizedBox(height: 24),
                            SizedBox(
                              width: double.infinity,
                              child: ElevatedButton(
                                onPressed: _selectedIssueId == null
                                    ? null
                                    : _submitForm,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.orange,
                                  padding: const EdgeInsets.symmetric(
                                    vertical: 16,
                                  ),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                ),
                                child: Text(
                                  'Return Book',
                                  style: GoogleFonts.montserrat(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w600,
                                    color: Colors.white,
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _submitForm() async {
    if (_formKey.currentState!.validate() && _selectedIssueId != null) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<LibraryProvider>();

      final fineAmount = double.tryParse(_fineAmountController.text) ?? 0.0;

      final success = await provider.returnBook(
        issueId: _selectedIssueId!,
        returnDate: DateFormat('yyyy-MM-dd').format(_returnDate),
        fineAmount: fineAmount > 0 ? fineAmount : null,
        remarks: _remarksController.text.isEmpty
            ? null
            : _remarksController.text,
        userId: user?.id,
      );

      if (mounted) {
        if (success) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'Book returned successfully!',
          );
          Navigator.pop(context);
        } else {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: provider.error ?? 'Failed to return book',
          );
        }
      }
    }
  }
}
