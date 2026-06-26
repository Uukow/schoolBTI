import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/attendance_provider.dart';
import '../../providers/student_provider.dart';
import '../../../core/constants.dart';

class AttendanceReportsPage extends StatefulWidget {
  const AttendanceReportsPage({super.key});

  @override
  State<AttendanceReportsPage> createState() => _AttendanceReportsPageState();
}

class _AttendanceReportsPageState extends State<AttendanceReportsPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  int? _selectedClassId;
  int? _selectedStudentId;
  DateTime _startDate = DateTime.now().subtract(const Duration(days: 30));
  DateTime _endDate = DateTime.now();

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<StudentProvider>().loadClasses();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _selectStartDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _startDate,
      firstDate: DateTime.now().subtract(const Duration(days: 365)),
      lastDate: DateTime.now(),
    );
    if (picked != null) {
      setState(() {
        _startDate = picked;
      });
      _loadReports();
    }
  }

  Future<void> _selectEndDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _endDate,
      firstDate: _startDate,
      lastDate: DateTime.now(),
    );
    if (picked != null) {
      setState(() {
        _endDate = picked;
      });
      _loadReports();
    }
  }

  void _loadReports() {
    final startDateStr = DateFormat('yyyy-MM-dd').format(_startDate);
    final endDateStr = DateFormat('yyyy-MM-dd').format(_endDate);

    if (_tabController.index == 0) {
      // Student Reports
      if (_selectedStudentId != null) {
        context.read<AttendanceProvider>().loadStats(
          studentId: _selectedStudentId,
          startDate: startDateStr,
          endDate: endDateStr,
        );
        context.read<AttendanceProvider>().loadAttendanceHistory(
          studentId: _selectedStudentId,
          startDate: startDateStr,
          endDate: endDateStr,
        );
      } else if (_selectedClassId != null) {
        context.read<AttendanceProvider>().loadAttendanceHistory(
          classId: _selectedClassId,
          startDate: startDateStr,
          endDate: endDateStr,
        );
      }
    } else {
      // Class Reports
      if (_selectedClassId != null) {
        context.read<AttendanceProvider>().loadAttendanceHistory(
          classId: _selectedClassId,
          startDate: startDateStr,
          endDate: endDateStr,
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
          'Attendance Reports',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: AppConstants.primaryColor,
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          indicatorWeight: 3,
          onTap: (index) {
            setState(() {});
          },
          tabs: const [
            Tab(text: 'Student Report'),
            Tab(text: 'Class Report'),
          ],
        ),
      ),
      body: Column(
        children: [
          // Filters
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              children: [
                // Date Range
                Row(
                  children: [
                    Expanded(
                      child: InkWell(
                        onTap: _selectStartDate,
                        child: Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            border: Border.all(color: Colors.grey[300]!),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.calendar_today, size: 16),
                              const SizedBox(width: 8),
                              Text(
                                DateFormat('MMM d, yyyy').format(_startDate),
                                style: GoogleFonts.montserrat(fontSize: 12),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    const Text('to'),
                    const SizedBox(width: 8),
                    Expanded(
                      child: InkWell(
                        onTap: _selectEndDate,
                        child: Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            border: Border.all(color: Colors.grey[300]!),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.calendar_today, size: 16),
                              const SizedBox(width: 8),
                              Text(
                                DateFormat('MMM d, yyyy').format(_endDate),
                                style: GoogleFonts.montserrat(fontSize: 12),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Class Selection
                Consumer<StudentProvider>(
                  builder: (context, studentProvider, child) {
                    return DropdownButtonFormField<int>(
                      decoration: InputDecoration(
                        labelText: 'Select Class',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        prefixIcon: const Icon(Icons.class_),
                      ),
                      initialValue: _selectedClassId,
                      items: studentProvider.classes.map((classItem) {
                        return DropdownMenuItem<int>(
                          value: classItem.id,
                          child: Text(classItem.className),
                        );
                      }).toList(),
                      onChanged: (value) {
                        setState(() {
                          _selectedClassId = value;
                          _selectedStudentId = null;
                        });
                        if (value != null) {
                          context.read<StudentProvider>().loadSectionsByClass(
                            value,
                          );
                          context.read<StudentProvider>().loadStudents(
                            classId: value,
                          );
                          _loadReports();
                        }
                      },
                    );
                  },
                ),

                // Student Selection (for Student Report tab)
                if (_tabController.index == 0 && _selectedClassId != null) ...[
                  const SizedBox(height: 16),
                  Consumer<StudentProvider>(
                    builder: (context, studentProvider, child) {
                      final students = studentProvider.students
                          .where(
                            (s) =>
                                (s as dynamic)?.currentClassId ==
                                _selectedClassId,
                          )
                          .toList();

                      return DropdownButtonFormField<int>(
                        decoration: InputDecoration(
                          labelText: 'Select Student (Optional)',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixIcon: const Icon(Icons.person),
                        ),
                        initialValue: _selectedStudentId,
                        items: [
                          const DropdownMenuItem<int>(
                            value: null,
                            child: Text('All Students'),
                          ),
                          ...students.map((student) {
                            final s = student as dynamic;
                            final name =
                                '${s?.firstName ?? ''} ${s?.lastName ?? ''}'
                                    .trim();
                            return DropdownMenuItem<int>(
                              value: s?.id ?? 0,
                              child: Text(
                                name.isEmpty ? 'Unknown Student' : name,
                              ),
                            );
                          }),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _selectedStudentId = value;
                          });
                          _loadReports();
                        },
                      );
                    },
                  ),
                ],
              ],
            ),
          ),

          // Reports Content
          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: [_buildStudentReport(), _buildClassReport()],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStudentReport() {
    return Consumer<AttendanceProvider>(
      builder: (context, provider, child) {
        if (_selectedClassId == null) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.bar_chart, size: 64, color: Colors.grey[400]),
                const SizedBox(height: 16),
                Text(
                  'Please select a class',
                  style: GoogleFonts.montserrat(
                    fontSize: 18,
                    color: Colors.grey[600],
                  ),
                ),
              ],
            ),
          );
        }

        if (provider.isLoading) {
          return const Center(child: CircularProgressIndicator());
        }

        final stats = provider.stats;
        final history = provider.attendanceHistory;

        return SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Statistics Card
              if (stats != null) ...[
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
                          'Statistics',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            Expanded(
                              child: _buildStatItem(
                                'Present',
                                stats.presentDays.toString(),
                                Colors.green,
                              ),
                            ),
                            Expanded(
                              child: _buildStatItem(
                                'Absent',
                                stats.absentDays.toString(),
                                Colors.red,
                              ),
                            ),
                            Expanded(
                              child: _buildStatItem(
                                'Late',
                                stats.lateDays.toString(),
                                Colors.orange,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Attendance Rate',
                              style: GoogleFonts.montserrat(
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            Text(
                              '${stats.attendanceRate.toStringAsFixed(1)}%',
                              style: GoogleFonts.montserrat(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                                color: stats.attendanceRate >= 75
                                    ? Colors.green
                                    : Colors.red,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
              ],

              // History List
              Text(
                'Attendance History',
                style: GoogleFonts.montserrat(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: AppConstants.primaryColor,
                ),
              ),
              const SizedBox(height: 12),
              if (history.isEmpty)
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Center(
                      child: Text(
                        'No attendance records found',
                        style: GoogleFonts.montserrat(color: Colors.grey[600]),
                      ),
                    ),
                  ),
                )
              else
                ...history.map((record) {
                  return Card(
                    margin: const EdgeInsets.only(bottom: 8),
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: _getStatusColor(
                          record.status,
                        ).withOpacity(0.1),
                        child: Icon(
                          _getStatusIcon(record.status),
                          color: _getStatusColor(record.status),
                          size: 20,
                        ),
                      ),
                      title: Text(
                        DateFormat(
                          'EEEE, MMM d, yyyy',
                        ).format(record.attendanceDate),
                        style: GoogleFonts.montserrat(
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      subtitle: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Status: ${record.status}',
                            style: GoogleFonts.montserrat(fontSize: 12),
                          ),
                          if (record.remarks != null &&
                              record.remarks!.isNotEmpty)
                            Text(
                              'Remarks: ${record.remarks}',
                              style: GoogleFonts.montserrat(fontSize: 12),
                            ),
                        ],
                      ),
                      trailing: Text(
                        record.status,
                        style: GoogleFonts.montserrat(
                          fontWeight: FontWeight.bold,
                          color: _getStatusColor(record.status),
                        ),
                      ),
                    ),
                  );
                }),
            ],
          ),
        );
      },
    );
  }

  Widget _buildClassReport() {
    return Consumer<AttendanceProvider>(
      builder: (context, provider, child) {
        if (_selectedClassId == null) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.class_, size: 64, color: Colors.grey[400]),
                const SizedBox(height: 16),
                Text(
                  'Please select a class',
                  style: GoogleFonts.montserrat(
                    fontSize: 18,
                    color: Colors.grey[600],
                  ),
                ),
              ],
            ),
          );
        }

        if (provider.isLoading) {
          return const Center(child: CircularProgressIndicator());
        }

        final history = provider.attendanceHistory;

        // Group by student
        final Map<int, List> studentsMap = {};
        for (var record in history) {
          if (!studentsMap.containsKey(record.studentId)) {
            studentsMap[record.studentId] = [];
          }
          studentsMap[record.studentId]!.add(record);
        }

        if (studentsMap.isEmpty) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.bar_chart, size: 64, color: Colors.grey[400]),
                const SizedBox(height: 16),
                Text(
                  'No attendance records found',
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
          itemCount: studentsMap.length,
          itemBuilder: (context, index) {
            final studentId = studentsMap.keys.elementAt(index);
            final records = studentsMap[studentId]!;
            final firstRecord = records[0] as dynamic;
            final presentCount = records
                .where((r) => (r as dynamic).status == 'Present')
                .length;
            final totalCount = records.length;
            final rate = totalCount > 0
                ? (presentCount / totalCount) * 100
                : 0.0;

            return Card(
              margin: const EdgeInsets.only(bottom: 12),
              elevation: 2,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
              child: ExpansionTile(
                leading: CircleAvatar(
                  backgroundColor: AppConstants.primaryColor.withOpacity(0.1),
                  child: Text(
                    (firstRecord.studentName as String)[0].toUpperCase(),
                    style: TextStyle(
                      color: AppConstants.primaryColor,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                title: Text(
                  firstRecord.studentName,
                  style: GoogleFonts.montserrat(fontWeight: FontWeight.w600),
                ),
                subtitle: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: 4),
                    Text(
                      'Present: $presentCount / $totalCount',
                      style: GoogleFonts.montserrat(fontSize: 12),
                    ),
                    Text(
                      'Rate: ${rate.toStringAsFixed(1)}%',
                      style: GoogleFonts.montserrat(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: rate >= 75 ? Colors.green : Colors.red,
                      ),
                    ),
                  ],
                ),
                children: records.map<Widget>((record) {
                  final r = record as dynamic;
                  return ListTile(
                    leading: Icon(
                      _getStatusIcon(r.status),
                      color: _getStatusColor(r.status),
                      size: 20,
                    ),
                    title: Text(
                      DateFormat('MMM d, yyyy').format(r.attendanceDate),
                      style: GoogleFonts.montserrat(fontSize: 14),
                    ),
                    trailing: Text(
                      r.status,
                      style: GoogleFonts.montserrat(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: _getStatusColor(r.status),
                      ),
                    ),
                  );
                }).toList(),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildStatItem(String label, String value, Color color) {
    return Column(
      children: [
        Text(
          value,
          style: GoogleFonts.montserrat(
            fontSize: 24,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        Text(
          label,
          style: GoogleFonts.montserrat(fontSize: 12, color: Colors.grey[600]),
        ),
      ],
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'Present':
        return Colors.green;
      case 'Absent':
        return Colors.red;
      case 'Late':
        return Colors.orange;
      case 'Half Day':
        return Colors.blue;
      case 'Leave':
        return Colors.purple;
      default:
        return Colors.grey;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status) {
      case 'Present':
        return Icons.check_circle;
      case 'Absent':
        return Icons.cancel;
      case 'Late':
        return Icons.schedule;
      case 'Half Day':
        return Icons.hourglass_bottom;
      case 'Leave':
        return Icons.beach_access;
      default:
        return Icons.help_outline;
    }
  }
}
