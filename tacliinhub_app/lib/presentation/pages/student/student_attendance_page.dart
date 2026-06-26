import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:fl_chart/fl_chart.dart';
import '../../../core/constants.dart';
import '../../providers/student_portal_provider.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/role_based_drawer.dart';
import '../../../data/models/student_models.dart';

class StudentAttendancePage extends StatefulWidget {
  const StudentAttendancePage({super.key});

  @override
  State<StudentAttendancePage> createState() => _StudentAttendancePageState();
}

class _StudentAttendancePageState extends State<StudentAttendancePage> {
  DateTime? _startDate;
  DateTime? _endDate;

  @override
  void initState() {
    super.initState();
    _endDate = DateTime.now();
    _startDate = DateTime.now().subtract(
      const Duration(days: 365),
    ); // Load full year by default
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadAttendance();
    });
  }

  void _loadAttendance() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    if (user != null) {
      Provider.of<StudentPortalProvider>(context, listen: false).loadAttendance(
        userId: user.id,
        startDate: _startDate,
        endDate: _endDate,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'My Attendance',
          style: GoogleFonts.montserrat(fontWeight: FontWeight.bold),
        ),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _loadAttendance,
            tooltip: 'Refresh',
          ),
        ],
      ),
      drawer: const RoleBasedDrawer(),
      body: Consumer<StudentPortalProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.attendanceStats == null) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null && provider.attendanceStats == null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 64, color: Colors.red),
                  const SizedBox(height: 16),
                  Text(provider.error ?? 'Error loading attendance'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: _loadAttendance,
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          final stats = provider.attendanceStats;
          if (stats == null || provider.attendance.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.how_to_reg_outlined,
                    size: 64,
                    color: Colors.grey[400],
                  ),
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

          return RefreshIndicator(
            onRefresh: () async => _loadAttendance(),
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Date Filter
                  Row(
                    children: [
                      Expanded(
                        child: InkWell(
                          onTap: () async {
                            final date = await showDatePicker(
                              context: context,
                              initialDate: _startDate ?? DateTime.now(),
                              firstDate: DateTime(2020),
                              lastDate: DateTime.now(),
                            );
                            if (date != null) {
                              setState(() {
                                _startDate = date;
                              });
                              _loadAttendance();
                            }
                          },
                          child: Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              border: Border.all(color: Colors.grey[300]!),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.calendar_today, size: 18),
                                const SizedBox(width: 8),
                                Text(
                                  _startDate != null
                                      ? DateFormat(
                                          'MMM d, yyyy',
                                        ).format(_startDate!)
                                      : 'Start Date',
                                  style: GoogleFonts.montserrat(),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: InkWell(
                          onTap: () async {
                            final date = await showDatePicker(
                              context: context,
                              initialDate: _endDate ?? DateTime.now(),
                              firstDate: DateTime(2020),
                              lastDate: DateTime.now(),
                            );
                            if (date != null) {
                              setState(() {
                                _endDate = date;
                              });
                              _loadAttendance();
                            }
                          },
                          child: Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              border: Border.all(color: Colors.grey[300]!),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.calendar_today, size: 18),
                                const SizedBox(width: 8),
                                Text(
                                  _endDate != null
                                      ? DateFormat(
                                          'MMM d, yyyy',
                                        ).format(_endDate!)
                                      : 'End Date',
                                  style: GoogleFonts.montserrat(),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // Pie Chart
                  _buildPieChart(stats['overall']),
                  const SizedBox(height: 24),

                  // Overall Attendance Card
                  _buildOverallAttendanceCard(stats['overall']),
                  const SizedBox(height: 16),

                  // Absence Percentage Cards
                  _buildAbsenceCards(stats['time_periods']),
                  const SizedBox(height: 24),

                  // Attendance by Subject Table
                  _buildSubjectTable(stats['by_subject']),
                  const SizedBox(height: 24),

                  // Attendance Records Table
                  _buildAttendanceRecordsTable(provider.attendance),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildPieChart(Map<String, dynamic>? overall) {
    if (overall == null) return const SizedBox.shrink();

    final present = overall['present'] ?? 0;
    final absent = overall['absent'] ?? 0;
    final late = overall['late'] ?? 0;
    final leave = overall['leave'] ?? 0;
    final total = overall['total_days'] ?? 0;

    if (total == 0) {
      return Container(
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Center(
          child: Text(
            'No attendance data available',
            style: GoogleFonts.montserrat(color: Colors.grey[600]),
          ),
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          Text(
            'Attendance Overview',
            style: GoogleFonts.montserrat(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Colors.grey[900],
            ),
          ),
          const SizedBox(height: 24),
          SizedBox(
            height: 200,
            child: PieChart(
              PieChartData(
                sectionsSpace: 2,
                centerSpaceRadius: 60,
                sections: [
                  if (present > 0)
                    PieChartSectionData(
                      value: present.toDouble(),
                      title: '${((present / total) * 100).toStringAsFixed(0)}%',
                      color: Colors.green,
                      radius: 50,
                      titleStyle: GoogleFonts.montserrat(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                  if (absent > 0)
                    PieChartSectionData(
                      value: absent.toDouble(),
                      title: '${((absent / total) * 100).toStringAsFixed(0)}%',
                      color: Colors.red,
                      radius: 50,
                      titleStyle: GoogleFonts.montserrat(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                  if (late > 0)
                    PieChartSectionData(
                      value: late.toDouble(),
                      title: '${((late / total) * 100).toStringAsFixed(0)}%',
                      color: Colors.orange,
                      radius: 50,
                      titleStyle: GoogleFonts.montserrat(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                  if (leave > 0)
                    PieChartSectionData(
                      value: leave.toDouble(),
                      title: '${((leave / total) * 100).toStringAsFixed(0)}%',
                      color: Colors.blue,
                      radius: 50,
                      titleStyle: GoogleFonts.montserrat(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _buildLegendItem('Present', Colors.green),
              const SizedBox(width: 16),
              _buildLegendItem('Absent', Colors.red),
              const SizedBox(width: 16),
              _buildLegendItem('Late', Colors.orange),
              if (leave > 0) ...[
                const SizedBox(width: 16),
                _buildLegendItem('Leave', Colors.blue),
              ],
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildLegendItem(String label, Color color) {
    return Row(
      children: [
        Container(
          width: 12,
          height: 12,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
        ),
        const SizedBox(width: 6),
        Text(label, style: GoogleFonts.montserrat(fontSize: 12)),
      ],
    );
  }

  Widget _buildOverallAttendanceCard(Map<String, dynamic>? overall) {
    if (overall == null) return const SizedBox.shrink();

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            AppConstants.primaryColor,
            AppConstants.primaryColor.withOpacity(0.8),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Overall Attendance (All Subjects Combined)',
            style: GoogleFonts.montserrat(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 20),
          Row(
            children: [
              Expanded(
                child: _buildStatItem(
                  'Total Days',
                  '${overall['total_days'] ?? 0}',
                  Colors.white,
                ),
              ),
              Expanded(
                child: _buildStatItem(
                  'Present',
                  '${overall['present'] ?? 0}',
                  Colors.white,
                ),
              ),
              Expanded(
                child: _buildStatItem(
                  'Absent',
                  '${overall['absent'] ?? 0}',
                  Colors.white,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Overall Attendance %',
                  style: GoogleFonts.montserrat(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: Colors.white,
                  ),
                ),
                Text(
                  '${overall['attendance_percentage']?.toStringAsFixed(2) ?? '0.00'}%',
                  style: GoogleFonts.montserrat(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatItem(String label, String value, Color textColor) {
    return Column(
      children: [
        Text(
          value,
          style: GoogleFonts.montserrat(
            fontSize: 24,
            fontWeight: FontWeight.bold,
            color: textColor,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          style: GoogleFonts.montserrat(
            fontSize: 12,
            color: textColor.withOpacity(0.9),
          ),
        ),
      ],
    );
  }

  Widget _buildAbsenceCards(Map<String, dynamic>? timePeriods) {
    if (timePeriods == null) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Absence Percentage (All Subjects)',
          style: GoogleFonts.montserrat(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Colors.grey[900],
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildAbsenceCard(
                'This Week',
                timePeriods['this_week'] ?? {},
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildAbsenceCard(
                'This Month',
                timePeriods['this_month'] ?? {},
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildAbsenceCard(
                'This Year',
                timePeriods['this_year'] ?? {},
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildAbsenceCard(String period, Map<String, dynamic> data) {
    final totalDays = data['total_days'] ?? 0;
    final absent = data['absent'] ?? 0;
    final percentage = data['absence_percentage'] ?? 0.0;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[200]!),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 5,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            period,
            style: GoogleFonts.montserrat(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: Colors.grey[700],
            ),
          ),
          const SizedBox(height: 8),
          Text(
            '${percentage.toStringAsFixed(2)}%',
            style: GoogleFonts.montserrat(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: percentage > 20 ? Colors.red : Colors.orange,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            '$absent absences out of $totalDays days',
            style: GoogleFonts.montserrat(
              fontSize: 11,
              color: Colors.grey[600],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSubjectTable(List<dynamic>? bySubject) {
    if (bySubject == null || bySubject.isEmpty) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Attendance by Subject',
          style: GoogleFonts.montserrat(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Colors.grey[900],
          ),
        ),
        const SizedBox(height: 12),
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: DataTable(
              headingRowColor: WidgetStateProperty.all(
                AppConstants.primaryColor.withOpacity(0.1),
              ),
              columns: [
                DataColumn(
                  label: Text(
                    'Subject',
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.bold,
                      color: AppConstants.primaryColor,
                    ),
                  ),
                ),
                DataColumn(
                  label: Text(
                    'Total Days',
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.bold,
                      color: AppConstants.primaryColor,
                    ),
                  ),
                ),
                DataColumn(
                  label: Text(
                    'Present',
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.bold,
                      color: AppConstants.primaryColor,
                    ),
                  ),
                ),
                DataColumn(
                  label: Text(
                    'Absent',
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.bold,
                      color: AppConstants.primaryColor,
                    ),
                  ),
                ),
                DataColumn(
                  label: Text(
                    'Late',
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.bold,
                      color: AppConstants.primaryColor,
                    ),
                  ),
                ),
                DataColumn(
                  label: Text(
                    'Leave',
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.bold,
                      color: AppConstants.primaryColor,
                    ),
                  ),
                ),
                DataColumn(
                  label: Text(
                    'Attendance %',
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.bold,
                      color: AppConstants.primaryColor,
                    ),
                  ),
                ),
              ],
              rows: bySubject.map<DataRow>((subject) {
                final percentage = subject['attendance_percentage'] ?? 0.0;
                return DataRow(
                  cells: [
                    DataCell(
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            subject['subject_name'] ?? '',
                            style: GoogleFonts.montserrat(
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          Text(
                            subject['subject_code'] ?? '',
                            style: GoogleFonts.montserrat(
                              fontSize: 11,
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ),
                    ),
                    DataCell(Text('${subject['total_days'] ?? 0}')),
                    DataCell(Text('${subject['present'] ?? 0}')),
                    DataCell(Text('${subject['absent'] ?? 0}')),
                    DataCell(Text('${subject['late'] ?? 0}')),
                    DataCell(Text('${subject['leave'] ?? 0}')),
                    DataCell(
                      Text(
                        '${percentage.toStringAsFixed(0)}%',
                        style: GoogleFonts.montserrat(
                          fontWeight: FontWeight.w600,
                          color: percentage >= 75
                              ? Colors.green
                              : percentage >= 50
                              ? Colors.orange
                              : Colors.red,
                        ),
                      ),
                    ),
                  ],
                );
              }).toList(),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildAttendanceRecordsTable(List<StudentAttendance> attendance) {
    if (attendance.isEmpty) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Attendance Records',
          style: GoogleFonts.montserrat(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Colors.grey[900],
          ),
        ),
        const SizedBox(height: 12),
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: DataTable(
              headingRowColor: WidgetStateProperty.all(
                AppConstants.primaryColor.withOpacity(0.1),
              ),
              columns: [
                DataColumn(
                  label: Text(
                    'Date',
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.bold,
                      color: AppConstants.primaryColor,
                    ),
                  ),
                ),
                DataColumn(
                  label: Text(
                    'Subject',
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.bold,
                      color: AppConstants.primaryColor,
                    ),
                  ),
                ),
                DataColumn(
                  label: Text(
                    'Status',
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.bold,
                      color: AppConstants.primaryColor,
                    ),
                  ),
                ),
                DataColumn(
                  label: Text(
                    'Remarks',
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.bold,
                      color: AppConstants.primaryColor,
                    ),
                  ),
                ),
              ],
              rows: attendance.map<DataRow>((record) {
                return DataRow(
                  cells: [
                    DataCell(
                      Text(
                        DateFormat('MMM d, yyyy').format(record.date),
                        style: GoogleFonts.montserrat(),
                      ),
                    ),
                    DataCell(
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            record.subjectName,
                            style: GoogleFonts.montserrat(
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          if (record.subjectCode != null)
                            Text(
                              record.subjectCode!,
                              style: GoogleFonts.montserrat(
                                fontSize: 11,
                                color: Colors.grey[600],
                              ),
                            ),
                        ],
                      ),
                    ),
                    DataCell(
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: _getStatusColor(
                            record.status,
                          ).withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          record.status,
                          style: GoogleFonts.montserrat(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: _getStatusColor(record.status),
                          ),
                        ),
                      ),
                    ),
                    DataCell(
                      Text(
                        record.remarks ?? '-',
                        style: GoogleFonts.montserrat(
                          fontSize: 12,
                          color: Colors.grey[600],
                        ),
                      ),
                    ),
                  ],
                );
              }).toList(),
            ),
          ),
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
      case 'Leave':
        return Colors.blue;
      default:
        return Colors.grey;
    }
  }
}
