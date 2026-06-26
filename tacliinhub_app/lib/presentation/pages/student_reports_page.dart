import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../providers/student_provider.dart';
import '../providers/class_provider.dart';
import '../../data/models/class_models.dart';

class StudentReportsPage extends StatefulWidget {
  const StudentReportsPage({super.key});

  @override
  State<StudentReportsPage> createState() => _StudentReportsPageState();
}

class _StudentReportsPageState extends State<StudentReportsPage> {
  String? _selectedReportType;
  SchoolClass? _selectedClass;
  Section? _selectedSection;
  bool _isGenerating = false;

  final List<Map<String, dynamic>> _reportTypes = [
    {
      'id': 'attendance',
      'name': 'Attendance Report',
      'icon': Icons.calendar_today,
      'color': Colors.blue,
    },
    {
      'id': 'performance',
      'name': 'Performance Report',
      'icon': Icons.assessment,
      'color': Colors.green,
    },
    {
      'id': 'fees',
      'name': 'Fee Report',
      'icon': Icons.attach_money,
      'color': Colors.orange,
    },
    {
      'id': 'complete',
      'name': 'Complete Report',
      'icon': Icons.description,
      'color': Colors.purple,
    },
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      // Load student stats and classes
      context.read<StudentProvider>().loadStats();
      context.read<StudentProvider>().loadStudents();
      context.read<ClassProvider>().loadClasses();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Student Reports',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: const Color(0xFF6D28D9),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Statistics Summary
            Consumer<StudentProvider>(
              builder: (context, provider, child) {
                final stats = provider.stats;

                if (stats != null) {
                  return Container(
                    padding: const EdgeInsets.all(16),
                    color: Colors.white,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Student Statistics',
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            Expanded(
                              child: _buildStatCard(
                                'Total',
                                stats.total.toString(),
                                Colors.blue,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: _buildStatCard(
                                'Active',
                                stats.active.toString(),
                                Colors.green,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(
                              child: _buildStatCard(
                                'Inactive',
                                stats.inactive.toString(),
                                Colors.orange,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: _buildStatCard(
                                'Graduated',
                                stats.graduated.toString(),
                                Colors.purple,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  );
                }

                return const SizedBox.shrink();
              },
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(16),
              color: Colors.white,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Select Report Type',
                    style: GoogleFonts.montserrat(
                      fontSize: 18,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 16),
                  GridView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    gridDelegate:
                        const SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: 2,
                          crossAxisSpacing: 12,
                          mainAxisSpacing: 12,
                          childAspectRatio: 1.5,
                        ),
                    itemCount: _reportTypes.length,
                    itemBuilder: (context, index) {
                      final report = _reportTypes[index];
                      final isSelected = _selectedReportType == report['id'];
                      return InkWell(
                        onTap: () {
                          setState(() {
                            _selectedReportType = report['id'];
                          });
                        },
                        child: Container(
                          decoration: BoxDecoration(
                            color: isSelected
                                ? (report['color'] as Color).withOpacity(0.1)
                                : Colors.grey[100],
                            border: Border.all(
                              color: isSelected
                                  ? (report['color'] as Color)
                                  : Colors.grey[300]!,
                              width: 2,
                            ),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                report['icon'] as IconData,
                                size: 32,
                                color: report['color'] as Color,
                              ),
                              const SizedBox(height: 8),
                              Text(
                                report['name'] as String,
                                textAlign: TextAlign.center,
                                style: GoogleFonts.montserrat(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Filters',
                    style: GoogleFonts.montserrat(
                      fontSize: 18,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Consumer<ClassProvider>(
                    builder: (context, provider, child) {
                      return _buildClassDropdown(provider.classes);
                    },
                  ),
                  const SizedBox(height: 16),
                  Consumer<ClassProvider>(
                    builder: (context, provider, child) {
                      return _buildSectionDropdown(provider.sections);
                    },
                  ),
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton.icon(
                      onPressed: _selectedReportType == null
                          ? null
                          : _generateReport,
                      icon: _isGenerating
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                valueColor: AlwaysStoppedAnimation<Color>(
                                  Colors.white,
                                ),
                              ),
                            )
                          : const Icon(Icons.file_download),
                      label: Text(
                        _isGenerating ? 'Generating...' : 'Generate Report',
                        style: GoogleFonts.montserrat(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF6D28D9),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        disabledBackgroundColor: Colors.grey[300],
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: OutlinedButton.icon(
                      onPressed: _selectedReportType == null
                          ? null
                          : _previewReport,
                      icon: const Icon(Icons.preview),
                      label: Text(
                        'Preview Report',
                        style: GoogleFonts.montserrat(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF6D28D9),
                        side: BorderSide(
                          color: _selectedReportType == null
                              ? Colors.grey[300]!
                              : const Color(0xFF6D28D9),
                          width: 2,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard(String label, String value, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Column(
        children: [
          Text(
            value,
            style: GoogleFonts.montserrat(
              fontSize: 24,
              fontWeight: FontWeight.w700,
              color: color,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: GoogleFonts.montserrat(
              fontSize: 12,
              color: Colors.grey[700],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildClassDropdown(List<SchoolClass> classes) {
    return DropdownButtonFormField<SchoolClass>(
      decoration: InputDecoration(
        labelText: 'Class',
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        filled: true,
        fillColor: Colors.white,
      ),
      initialValue: _selectedClass,
      items: [
        DropdownMenuItem<SchoolClass>(
          value: null,
          child: Text('All Classes', style: GoogleFonts.montserrat()),
        ),
        ...classes.map((SchoolClass cls) {
          return DropdownMenuItem<SchoolClass>(
            value: cls,
            child: Text(
              '${cls.className} (${cls.totalStudents})',
              style: GoogleFonts.montserrat(),
            ),
          );
        }),
      ],
      onChanged: (value) {
        setState(() {
          _selectedClass = value;
          _selectedSection = null;
        });
        if (value != null) {
          context.read<ClassProvider>().loadSectionsForClass(value.id);
        } else {
          context.read<ClassProvider>().clearSections();
        }
      },
      style: GoogleFonts.montserrat(color: Colors.black),
    );
  }

  Widget _buildSectionDropdown(List<Section> sections) {
    return DropdownButtonFormField<Section>(
      decoration: InputDecoration(
        labelText: 'Section',
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        filled: true,
        fillColor: Colors.white,
      ),
      initialValue: _selectedSection,
      items: [
        DropdownMenuItem<Section>(
          value: null,
          child: Text('All Sections', style: GoogleFonts.montserrat()),
        ),
        ...sections.map((Section sec) {
          return DropdownMenuItem<Section>(
            value: sec,
            child: Text(
              '${sec.sectionName} (${sec.currentStudents}/${sec.capacity})',
              style: GoogleFonts.montserrat(),
            ),
          );
        }),
      ],
      onChanged: (value) {
        setState(() {
          _selectedSection = value;
        });
      },
      style: GoogleFonts.montserrat(color: Colors.black),
    );
  }

  void _generateReport() async {
    setState(() {
      _isGenerating = true;
    });

    final params = {
      'class_id': _selectedClass?.id,
      'section_id': _selectedSection?.id,
    };

    final result = await context.read<StudentProvider>().generateReport(
      _selectedReportType!,
      params,
    );

    setState(() {
      _isGenerating = false;
    });

    if (result != null && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Report generated successfully!',
            style: GoogleFonts.montserrat(),
          ),
          backgroundColor: Colors.green,
        ),
      );
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Failed to generate report',
            style: GoogleFonts.montserrat(),
          ),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  void _previewReport() {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          'Preview feature coming soon...',
          style: GoogleFonts.montserrat(),
        ),
        backgroundColor: Colors.blue,
      ),
    );
  }
}
