import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../providers/student_provider.dart';
import '../providers/class_provider.dart';
import '../../data/models/class_models.dart';

class AssignSectionsPage extends StatefulWidget {
  const AssignSectionsPage({super.key});

  @override
  State<AssignSectionsPage> createState() => _AssignSectionsPageState();
}

class _AssignSectionsPageState extends State<AssignSectionsPage> {
  SchoolClass? _selectedClass;
  Section? _selectedSection;
  final List<int> _selectedStudents = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      // Load classes and students
      context.read<ClassProvider>().loadClasses();
      context.read<StudentProvider>().loadStudents();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Assign Sections',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: const Color(0xFF6D28D9),
        elevation: 0,
      ),
      body: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Select Section to Assign',
                  style: GoogleFonts.montserrat(
                    fontSize: 18,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(
                      child: Consumer<ClassProvider>(
                        builder: (context, provider, child) {
                          if (provider.isLoading && provider.classes.isEmpty) {
                            return const Center(
                              child: CircularProgressIndicator(),
                            );
                          }

                          return _buildClassDropdown(provider.classes);
                        },
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Consumer<ClassProvider>(
                        builder: (context, provider, child) {
                          return _buildSectionDropdown(provider.sections);
                        },
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.blue.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    children: [
                      const Icon(
                        Icons.info_outline,
                        color: Colors.blue,
                        size: 20,
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          'Select students below and assign them to ${_selectedClass?.className ?? 'selected class'} - ${_selectedSection?.sectionName ?? 'selected section'}',
                          style: GoogleFonts.montserrat(
                            fontSize: 12,
                            color: Colors.blue[900],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: Consumer<StudentProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading) {
                  return const Center(
                    child: CircularProgressIndicator(color: Color(0xFF6D28D9)),
                  );
                }

                if (provider.error != null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.error_outline,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          provider.error!,
                          style: GoogleFonts.montserrat(
                            fontSize: 16,
                            color: Colors.grey[600],
                          ),
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: () => provider.loadStudents(),
                          child: Text('Retry', style: GoogleFonts.montserrat()),
                        ),
                      ],
                    ),
                  );
                }

                final students = provider.students;

                if (students.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.school_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No students found',
                          style: GoogleFonts.montserrat(
                            fontSize: 16,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  );
                }

                return ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: students.length,
                  itemBuilder: (context, index) {
                    final student = students[index];
                    final isSelected = _selectedStudents.contains(student.id);

                    return Card(
                      margin: const EdgeInsets.only(bottom: 8),
                      child: CheckboxListTile(
                        value: isSelected,
                        onChanged: (value) {
                          setState(() {
                            if (value == true) {
                              _selectedStudents.add(student.id);
                            } else {
                              _selectedStudents.remove(student.id);
                            }
                          });
                        },
                        title: Text(
                          '${student.firstName} ${student.lastName}',
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text(
                              'ID: ${student.admissionNo}',
                              style: GoogleFonts.montserrat(fontSize: 12),
                            ),
                            Text(
                              'Current: ${student.className ?? 'No Class'} - ${student.sectionName ?? 'No Section'}',
                              style: GoogleFonts.montserrat(
                                fontSize: 12,
                                color: student.sectionName == null
                                    ? Colors.orange
                                    : Colors.grey[600],
                              ),
                            ),
                          ],
                        ),
                        secondary: CircleAvatar(
                          backgroundColor: const Color(
                            0xFF6D28D9,
                          ).withOpacity(0.1),
                          child: Text(
                            student.firstName[0].toUpperCase(),
                            style: GoogleFonts.montserrat(
                              color: const Color(0xFF6D28D9),
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                        activeColor: const Color(0xFF6D28D9),
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10),
          ],
        ),
        child: ElevatedButton(
          onPressed:
              _selectedStudents.isEmpty ||
                  _selectedClass == null ||
                  _selectedSection == null
              ? null
              : _assignSections,
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF6D28D9),
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            disabledBackgroundColor: Colors.grey[300],
          ),
          child: _isLoading
              ? const SizedBox(
                  height: 20,
                  width: 20,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                  ),
                )
              : Text(
                  _selectedClass != null && _selectedSection != null
                      ? 'Assign ${_selectedStudents.length} to ${_selectedClass!.className} - ${_selectedSection!.sectionName}'
                      : 'Select Class & Section',
                  style: GoogleFonts.montserrat(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                  ),
                  textAlign: TextAlign.center,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
        ),
      ),
    );
  }

  Widget _buildClassDropdown(List<SchoolClass> classes) {
    return DropdownButtonFormField<SchoolClass>(
      decoration: InputDecoration(
        labelText: 'Class',
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      ),
      initialValue: _selectedClass,
      items: classes.map((SchoolClass cls) {
        return DropdownMenuItem<SchoolClass>(
          value: cls,
          child: Text(
            '${cls.className} (${cls.totalStudents} students)',
            style: GoogleFonts.montserrat(fontSize: 14),
          ),
        );
      }).toList(),
      onChanged: (value) {
        setState(() {
          _selectedClass = value;
          _selectedSection = null;
          _selectedStudents.clear();
        });
        if (value != null) {
          context.read<ClassProvider>().loadSectionsForClass(value.id);
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
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      ),
      initialValue: _selectedSection,
      items: sections.map((Section sec) {
        return DropdownMenuItem<Section>(
          value: sec,
          child: Text(
            '${sec.sectionName} (${sec.currentStudents}/${sec.capacity})',
            style: GoogleFonts.montserrat(fontSize: 14),
          ),
        );
      }).toList(),
      onChanged: (value) {
        setState(() {
          _selectedSection = value;
        });
      },
      style: GoogleFonts.montserrat(color: Colors.black),
    );
  }

  void _assignSections() async {
    if (_selectedClass == null || _selectedSection == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Please select class and section',
            style: GoogleFonts.montserrat(),
          ),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    // Prepare assignments data
    final assignments = _selectedStudents.map((studentId) {
      return {
        'student_id': studentId,
        'class_id': _selectedClass!.id,
        'section_id': _selectedSection!.id,
      };
    }).toList();

    final success = await context.read<StudentProvider>().assignSections(
      assignments,
    );

    setState(() {
      _isLoading = false;
    });

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Sections assigned successfully!',
            style: GoogleFonts.montserrat(),
          ),
          backgroundColor: Colors.green,
        ),
      );
      setState(() {
        _selectedStudents.clear();
      });
      // Reload students
      if (mounted) {
        context.read<StudentProvider>().loadStudents();
      }
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Failed to assign sections',
            style: GoogleFonts.montserrat(),
          ),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
}
