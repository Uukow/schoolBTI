import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/library_provider.dart';
import '../../providers/student_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class IssueBookPage extends StatefulWidget {
  const IssueBookPage({super.key});

  @override
  State<IssueBookPage> createState() => _IssueBookPageState();
}

class _IssueBookPageState extends State<IssueBookPage> {
  final _formKey = GlobalKey<FormState>();
  int? _selectedBookId;
  int? _selectedStudentId;
  String _memberType = 'Student';
  DateTime _issueDate = DateTime.now();
  DateTime? _dueDate;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final libraryProvider = context.read<LibraryProvider>();
      libraryProvider.loadBooks(userId: user?.id);
      // Load categories silently
      libraryProvider.loadCategories(userId: user?.id).catchError((e) {
        print('Category loading error: $e');
      });
      context.read<StudentProvider>().loadClasses();
    });
    _dueDate = DateTime.now().add(const Duration(days: 14)); // Default 14 days
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Issue Book',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.green,
        elevation: 0,
      ),
      body: SingleChildScrollView(
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
                        'Issue Book',
                        style: GoogleFonts.montserrat(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 24),
                      Consumer<LibraryProvider>(
                        builder: (context, libraryProvider, child) {
                          final availableBooks = libraryProvider.availableBooks;
                          return DropdownButtonFormField<int>(
                            decoration: InputDecoration(
                              labelText: 'Select Book *',
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              prefixIcon: const Icon(Icons.book),
                            ),
                            initialValue: _selectedBookId,
                            items: availableBooks.isEmpty
                                ? [
                                    const DropdownMenuItem<int>(
                                      value: null,
                                      enabled: false,
                                      child: Text('No books available'),
                                    ),
                                  ]
                                : availableBooks.map((book) {
                                    return DropdownMenuItem<int>(
                                      value: book.id,
                                      child: Text(
                                        '${book.title}${book.author != null && book.author!.isNotEmpty ? ' - ${book.author}' : ''} (Available: ${book.availableCopies})',
                                      ),
                                    );
                                  }).toList(),
                            onChanged: availableBooks.isEmpty
                                ? null
                                : (value) {
                                    setState(() {
                                      _selectedBookId = value;
                                    });
                                  },
                            validator: (value) =>
                                value == null ? 'Please select a book' : null,
                          );
                        },
                      ),
                      const SizedBox(height: 16),
                      DropdownButtonFormField<String>(
                        decoration: InputDecoration(
                          labelText: 'Member Type *',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixIcon: const Icon(Icons.person),
                        ),
                        initialValue: _memberType,
                        items: const [
                          DropdownMenuItem(
                            value: 'Student',
                            child: Text('Student'),
                          ),
                          DropdownMenuItem(
                            value: 'Staff',
                            child: Text('Staff'),
                          ),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _memberType = value!;
                            _selectedStudentId = null; // Reset selection
                          });
                        },
                      ),
                      const SizedBox(height: 16),
                      Consumer<StudentProvider>(
                        builder: (context, studentProvider, child) {
                          return DropdownButtonFormField<int>(
                            decoration: InputDecoration(
                              labelText: 'Select $_memberType *',
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              prefixIcon: const Icon(Icons.person),
                            ),
                            initialValue: _selectedStudentId,
                            items: studentProvider.students.map((student) {
                              final s = student as dynamic;
                              final name =
                                  '${s?.firstName ?? ''} ${s?.lastName ?? ''}'
                                      .trim();
                              final admissionNo = s?.admissionNo ?? '';
                              return DropdownMenuItem<int>(
                                value: s?.id ?? 0,
                                child: Text(
                                  name.isEmpty
                                      ? 'Unknown Student'
                                      : '$name${admissionNo.isNotEmpty ? ' ($admissionNo)' : ''}',
                                ),
                              );
                            }).toList(),
                            onChanged: (value) {
                              setState(() {
                                _selectedStudentId = value;
                              });
                            },
                            validator: (value) => value == null
                                ? 'Please select $_memberType'
                                : null,
                          );
                        },
                      ),
                      const SizedBox(height: 16),
                      InkWell(
                        onTap: () async {
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: _issueDate,
                            firstDate: DateTime.now().subtract(
                              const Duration(days: 30),
                            ),
                            lastDate: DateTime.now(),
                          );
                          if (picked != null) {
                            setState(() {
                              _issueDate = picked;
                            });
                          }
                        },
                        child: InputDecorator(
                          decoration: InputDecoration(
                            labelText: 'Issue Date *',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            prefixIcon: const Icon(Icons.calendar_today),
                          ),
                          child: Text(
                            DateFormat('yyyy-MM-dd').format(_issueDate),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      InkWell(
                        onTap: () async {
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: _dueDate ?? DateTime.now(),
                            firstDate: _issueDate,
                            lastDate: DateTime.now().add(
                              const Duration(days: 365),
                            ),
                          );
                          if (picked != null) {
                            setState(() {
                              _dueDate = picked;
                            });
                          }
                        },
                        child: InputDecorator(
                          decoration: InputDecoration(
                            labelText: 'Due Date *',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            prefixIcon: const Icon(Icons.event),
                          ),
                          child: Text(
                            _dueDate != null
                                ? DateFormat('yyyy-MM-dd').format(_dueDate!)
                                : 'Select due date',
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: _submitForm,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.green,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          child: Text(
                            'Issue Book',
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
    );
  }

  Future<void> _submitForm() async {
    if (_formKey.currentState!.validate() && _dueDate != null) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<LibraryProvider>();

      final success = await provider.issueBook(
        bookId: _selectedBookId!,
        memberId: _selectedStudentId!,
        memberType: _memberType,
        issueDate: DateFormat('yyyy-MM-dd').format(_issueDate),
        dueDate: DateFormat('yyyy-MM-dd').format(_dueDate!),
        userId: user?.id,
      );

      if (mounted) {
        if (success) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'Book issued successfully!',
          );
          Navigator.pop(context);
        } else {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: provider.error ?? 'Failed to issue book',
          );
        }
      }
    }
  }
}
