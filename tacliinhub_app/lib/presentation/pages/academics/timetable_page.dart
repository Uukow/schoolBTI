import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/academic_provider.dart';
import '../../providers/student_provider.dart';

class TimetablePage extends StatefulWidget {
  const TimetablePage({super.key});

  @override
  State<TimetablePage> createState() => _TimetablePageState();
}

class _TimetablePageState extends State<TimetablePage> {
  int? _selectedClassId;
  int? _selectedSectionId;

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
          'Timetable',
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
          // Class and Section selectors
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              children: [
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
                          _selectedSectionId = null;
                        });
                        if (value != null) {
                          context.read<StudentProvider>().loadSectionsByClass(
                            value,
                          );
                        }
                      },
                    );
                  },
                ),
                const SizedBox(height: 12),
                Consumer<StudentProvider>(
                  builder: (context, studentProvider, child) {
                    return DropdownButtonFormField<int>(
                      decoration: InputDecoration(
                        labelText: 'Select Section',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        prefixIcon: const Icon(Icons.group),
                      ),
                      initialValue: _selectedSectionId,
                      items: studentProvider.sections.map((section) {
                        return DropdownMenuItem<int>(
                          value: section.id,
                          child: Text(section.sectionName),
                        );
                      }).toList(),
                      onChanged: _selectedClassId == null
                          ? null
                          : (value) {
                              setState(() {
                                _selectedSectionId = value;
                              });
                              if (value != null && _selectedClassId != null) {
                                context.read<AcademicProvider>().loadTimetable(
                                  classId: _selectedClassId!,
                                  sectionId: value,
                                );
                              }
                            },
                    );
                  },
                ),
              ],
            ),
          ),
          // Timetable
          Expanded(
            child: Consumer<AcademicProvider>(
              builder: (context, provider, child) {
                if (_selectedClassId == null || _selectedSectionId == null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.schedule, size: 64, color: Colors.grey[400]),
                        const SizedBox(height: 16),
                        Text(
                          'Please select class and section',
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
                        Text(provider.error ?? 'Error loading timetable'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: () => provider.loadTimetable(
                            classId: _selectedClassId!,
                            sectionId: _selectedSectionId!,
                          ),
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                if (provider.timetable.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.schedule, size: 64, color: Colors.grey[400]),
                        const SizedBox(height: 16),
                        Text(
                          'No timetable found',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  );
                }

                // Group by day
                final days = [
                  'Monday',
                  'Tuesday',
                  'Wednesday',
                  'Thursday',
                  'Friday',
                  'Saturday',
                  'Sunday',
                ];
                final timetableByDay = <String, List>{};
                for (var day in days) {
                  timetableByDay[day] = provider.timetable
                      .where((period) => period.dayOfWeek == day)
                      .toList();
                }

                return ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: days.length,
                  itemBuilder: (context, index) {
                    final day = days[index];
                    final periods = timetableByDay[day] ?? [];
                    if (periods.isEmpty) return const SizedBox.shrink();

                    return Card(
                      margin: const EdgeInsets.only(bottom: 16),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: Colors.purple.withOpacity(0.1),
                              borderRadius: const BorderRadius.only(
                                topLeft: Radius.circular(12),
                                topRight: Radius.circular(12),
                              ),
                            ),
                            child: Text(
                              day,
                              style: GoogleFonts.montserrat(
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                                color: Colors.purple[700],
                              ),
                            ),
                          ),
                          ...periods.map((period) {
                            return ListTile(
                              leading: const Icon(Icons.access_time),
                              title: Text(
                                period.subjectName,
                                style: GoogleFonts.montserrat(
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                              subtitle: Text(
                                '${period.startTime} - ${period.endTime}',
                                style: GoogleFonts.montserrat(fontSize: 12),
                              ),
                              trailing: period.roomNo != null
                                  ? Text(
                                      'Room: ${period.roomNo}',
                                      style: GoogleFonts.montserrat(
                                        fontSize: 12,
                                      ),
                                    )
                                  : null,
                            );
                          }),
                        ],
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
}
