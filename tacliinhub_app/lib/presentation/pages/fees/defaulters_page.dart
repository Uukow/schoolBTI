import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/fees_provider.dart';
import '../../providers/student_provider.dart';

class DefaultersPage extends StatefulWidget {
  const DefaultersPage({super.key});

  @override
  State<DefaultersPage> createState() => _DefaultersPageState();
}

class _DefaultersPageState extends State<DefaultersPage> {
  int? _selectedClassId;
  int? _selectedFeeTypeId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<FeesProvider>().loadFeeTypes();
      context.read<FeesProvider>().loadDefaulters();
      context.read<StudentProvider>().loadClasses();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Fee Defaulters',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.red,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Filters
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              children: [
                Consumer<FeesProvider>(
                  builder: (context, feesProvider, child) {
                    return DropdownButtonFormField<int>(
                      decoration: InputDecoration(
                        labelText: 'Fee Type',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        prefixIcon: const Icon(Icons.account_balance),
                      ),
                      initialValue: _selectedFeeTypeId,
                      items: [
                        const DropdownMenuItem<int>(
                          value: null,
                          child: Text('All Fee Types'),
                        ),
                        ...feesProvider.feeTypes.map((type) {
                          return DropdownMenuItem<int>(
                            value: type.id,
                            child: Text(type.feeName),
                          );
                        }),
                      ],
                      onChanged: (value) {
                        setState(() {
                          _selectedFeeTypeId = value;
                        });
                        _loadDefaulters();
                      },
                    );
                  },
                ),
                const SizedBox(height: 16),
                Consumer<StudentProvider>(
                  builder: (context, studentProvider, child) {
                    return DropdownButtonFormField<int>(
                      decoration: InputDecoration(
                        labelText: 'Class',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        prefixIcon: const Icon(Icons.class_),
                      ),
                      initialValue: _selectedClassId,
                      items: [
                        const DropdownMenuItem<int>(
                          value: null,
                          child: Text('All Classes'),
                        ),
                        ...studentProvider.classes.map((classItem) {
                          return DropdownMenuItem<int>(
                            value: classItem.id,
                            child: Text(classItem.className),
                          );
                        }),
                      ],
                      onChanged: (value) {
                        setState(() {
                          _selectedClassId = value;
                        });
                        _loadDefaulters();
                      },
                    );
                  },
                ),
              ],
            ),
          ),

          // Defaulters List
          Expanded(
            child: Consumer<FeesProvider>(
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
                        Text(provider.error ?? 'Error loading defaulters'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadDefaulters,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                var defaulters = provider.defaulters;
                if (_selectedClassId != null) {
                  defaulters = defaulters
                      .where((d) => d.classId == _selectedClassId)
                      .toList();
                }
                if (_selectedFeeTypeId != null) {
                  defaulters = defaulters
                      .where((d) => d.feeTypeId == _selectedFeeTypeId)
                      .toList();
                }

                // Filter for overdue/partially paid
                defaulters = defaulters
                    .where(
                      (d) =>
                          d.status == 'Overdue' ||
                          d.status == 'Assigned' ||
                          (d.status == 'Partially Paid' && d.dueAmount > 0),
                    )
                    .toList();

                if (defaulters.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.check_circle_outline,
                          size: 64,
                          color: Colors.green[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No defaulters found',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            color: Colors.grey[600],
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'All fees are up to date!',
                          style: GoogleFonts.montserrat(
                            fontSize: 14,
                            color: Colors.grey[500],
                          ),
                        ),
                      ],
                    ),
                  );
                }

                // Calculate total due amount
                final totalDue = defaulters.fold<double>(
                  0.0,
                  (sum, defaulter) => sum + defaulter.dueAmount,
                );

                return Column(
                  children: [
                    // Summary Card
                    Container(
                      margin: const EdgeInsets.all(16),
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: Colors.red,
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.red.withOpacity(0.3),
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
                                'Total Defaulters',
                                style: GoogleFonts.montserrat(
                                  fontSize: 14,
                                  color: Colors.white70,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                '${defaulters.length}',
                                style: GoogleFonts.montserrat(
                                  fontSize: 32,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.white,
                                ),
                              ),
                            ],
                          ),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Text(
                                'Total Due Amount',
                                style: GoogleFonts.montserrat(
                                  fontSize: 14,
                                  color: Colors.white70,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                '\$${totalDue.toStringAsFixed(2)}',
                                style: GoogleFonts.montserrat(
                                  fontSize: 24,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.white,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),

                    // Defaulters List
                    Expanded(
                      child: ListView.builder(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        itemCount: defaulters.length,
                        itemBuilder: (context, index) {
                          final defaulter = defaulters[index];
                          final isOverdue =
                              defaulter.status == 'Overdue' ||
                              (defaulter.dueDate != null &&
                                  defaulter.dueDate!.isBefore(DateTime.now()));

                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            elevation: 2,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                              side: BorderSide(
                                color: isOverdue ? Colors.red : Colors.orange,
                                width: 2,
                              ),
                            ),
                            child: ListTile(
                              leading: CircleAvatar(
                                backgroundColor: isOverdue
                                    ? Colors.red.withOpacity(0.1)
                                    : Colors.orange.withOpacity(0.1),
                                child: Icon(
                                  isOverdue ? Icons.warning : Icons.pending,
                                  color: isOverdue ? Colors.red : Colors.orange,
                                ),
                              ),
                              title: Text(
                                defaulter.studentName,
                                style: GoogleFonts.montserrat(
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                              subtitle: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const SizedBox(height: 4),
                                  Text(
                                    '${defaulter.className} - ${defaulter.feeTypeName}',
                                  ),
                                  Text(
                                    'Month: ${defaulter.month}',
                                    style: GoogleFonts.montserrat(fontSize: 12),
                                  ),
                                  Text(
                                    'Assigned: ${defaulter.assignedAmount.toStringAsFixed(2)} | Paid: ${defaulter.paidAmount.toStringAsFixed(2)}',
                                    style: GoogleFonts.montserrat(fontSize: 12),
                                  ),
                                  if (defaulter.dueDate != null)
                                    Text(
                                      'Due: ${DateFormat('MMM d, yyyy').format(defaulter.dueDate!)}',
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
                                ],
                              ),
                              trailing: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Text(
                                    '\$${defaulter.dueAmount.toStringAsFixed(2)}',
                                    style: GoogleFonts.montserrat(
                                      fontSize: 18,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.red,
                                    ),
                                  ),
                                  Chip(
                                    label: Text(defaulter.status),
                                    backgroundColor: isOverdue
                                        ? Colors.red.withOpacity(0.1)
                                        : Colors.orange.withOpacity(0.1),
                                    labelStyle: TextStyle(
                                      color: isOverdue
                                          ? Colors.red
                                          : Colors.orange,
                                      fontSize: 10,
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

  void _loadDefaulters() {
    context.read<FeesProvider>().loadDefaulters(
      classId: _selectedClassId,
      feeTypeId: _selectedFeeTypeId,
    );
  }
}
