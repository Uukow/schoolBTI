import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/fees_provider.dart';
import '../../providers/student_provider.dart';

class LedgerPage extends StatefulWidget {
  const LedgerPage({super.key});

  @override
  State<LedgerPage> createState() => _LedgerPageState();
}

class _LedgerPageState extends State<LedgerPage> {
  int? _selectedStudentId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<StudentProvider>().loadClasses();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Student Fee Ledger',
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
          // Student Selection
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Consumer<StudentProvider>(
              builder: (context, studentProvider, child) {
                return DropdownButtonFormField<int>(
                  decoration: InputDecoration(
                    labelText: 'Select Student',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    prefixIcon: const Icon(Icons.person),
                  ),
                  initialValue: _selectedStudentId,
                  items: studentProvider.students.map((student) {
                    final s = student as dynamic;
                    final name = '${s?.firstName ?? ''} ${s?.lastName ?? ''}'
                        .trim();
                    final admissionNo = s?.admissionNo ?? '';
                    final studentId = s?.id ?? 0;
                    return DropdownMenuItem<int>(
                      value: studentId,
                      child: Text(
                        name.isEmpty
                            ? 'Unknown Student (ID: $studentId)'
                            : '$name${admissionNo.isNotEmpty ? ' ($admissionNo)' : ' (ID: $studentId)'}',
                      ),
                    );
                  }).toList(),
                  onChanged: (value) {
                    setState(() {
                      _selectedStudentId = value;
                    });
                    if (value != null) {
                      context.read<FeesProvider>().loadStudentLedger(
                        studentId: value,
                      );
                    }
                  },
                );
              },
            ),
          ),

          // Ledger Entries
          Expanded(
            child: _selectedStudentId == null
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.book_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Please select a student',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  )
                : Consumer<FeesProvider>(
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
                              Text(provider.error ?? 'Error loading ledger'),
                              const SizedBox(height: 16),
                              ElevatedButton(
                                onPressed: () {
                                  provider.loadStudentLedger(
                                    studentId: _selectedStudentId!,
                                  );
                                },
                                child: const Text('Retry'),
                              ),
                            ],
                          ),
                        );
                      }

                      if (provider.ledger.isEmpty) {
                        return Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.book_outlined,
                                size: 64,
                                color: Colors.grey[400],
                              ),
                              const SizedBox(height: 16),
                              Text(
                                'No ledger entries found',
                                style: GoogleFonts.montserrat(
                                  fontSize: 18,
                                  color: Colors.grey[600],
                                ),
                              ),
                            ],
                          ),
                        );
                      }

                      // Calculate current balance
                      final currentBalance = provider.ledger.isNotEmpty
                          ? provider.ledger.last.balance
                          : 0.0;

                      return Column(
                        children: [
                          // Balance Summary Card
                          Container(
                            margin: const EdgeInsets.all(16),
                            padding: const EdgeInsets.all(20),
                            decoration: BoxDecoration(
                              color: Colors.orange,
                              borderRadius: BorderRadius.circular(16),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.orange.withOpacity(0.3),
                                  blurRadius: 10,
                                  offset: const Offset(0, 5),
                                ),
                              ],
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Current Balance',
                                      style: GoogleFonts.montserrat(
                                        fontSize: 14,
                                        color: Colors.white70,
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    Text(
                                      '\$${currentBalance.toStringAsFixed(2)}',
                                      style: GoogleFonts.montserrat(
                                        fontSize: 32,
                                        fontWeight: FontWeight.bold,
                                        color: Colors.white,
                                      ),
                                    ),
                                  ],
                                ),
                                Container(
                                  padding: const EdgeInsets.all(16),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withOpacity(0.2),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: const Icon(
                                    Icons.account_balance_wallet,
                                    color: Colors.white,
                                    size: 32,
                                  ),
                                ),
                              ],
                            ),
                          ),

                          // Ledger Entries List
                          Expanded(
                            child: ListView.builder(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 16,
                              ),
                              itemCount: provider.ledger.length,
                              itemBuilder: (context, index) {
                                final entry = provider.ledger[index];
                                final isDebit = entry.debit > 0;
                                final isCredit = entry.credit > 0;

                                return Card(
                                  margin: const EdgeInsets.only(bottom: 12),
                                  elevation: 2,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: ListTile(
                                    leading: CircleAvatar(
                                      backgroundColor: isDebit
                                          ? Colors.red.withOpacity(0.1)
                                          : Colors.green.withOpacity(0.1),
                                      child: Icon(
                                        isDebit
                                            ? Icons.arrow_downward
                                            : Icons.arrow_upward,
                                        color: isDebit
                                            ? Colors.red
                                            : Colors.green,
                                      ),
                                    ),
                                    title: Text(
                                      entry.transactionType,
                                      style: GoogleFonts.montserrat(
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                    subtitle: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        const SizedBox(height: 4),
                                        if (entry.description != null)
                                          Text(
                                            entry.description!,
                                            style: GoogleFonts.montserrat(
                                              fontSize: 12,
                                            ),
                                          ),
                                        Text(
                                          DateFormat(
                                            'MMM d, yyyy',
                                          ).format(entry.transactionDate),
                                          style: GoogleFonts.montserrat(
                                            fontSize: 12,
                                          ),
                                        ),
                                      ],
                                    ),
                                    trailing: Column(
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      crossAxisAlignment:
                                          CrossAxisAlignment.end,
                                      children: [
                                        if (isDebit)
                                          Text(
                                            '-\$${entry.debit.toStringAsFixed(2)}',
                                            style: GoogleFonts.montserrat(
                                              fontSize: 16,
                                              fontWeight: FontWeight.bold,
                                              color: Colors.red,
                                            ),
                                          ),
                                        if (isCredit)
                                          Text(
                                            '+\$${entry.credit.toStringAsFixed(2)}',
                                            style: GoogleFonts.montserrat(
                                              fontSize: 16,
                                              fontWeight: FontWeight.bold,
                                              color: Colors.green,
                                            ),
                                          ),
                                        Text(
                                          'Balance: \$${entry.balance.toStringAsFixed(2)}',
                                          style: GoogleFonts.montserrat(
                                            fontSize: 12,
                                            color: Colors.grey[600],
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                );
                              },
                            ),
                          ),
                        ],
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}
