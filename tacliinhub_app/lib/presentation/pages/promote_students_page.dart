import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../providers/student_provider.dart';
import '../providers/class_provider.dart';
import '../../data/models/class_models.dart';

class PromoteStudentsPage extends StatefulWidget {
  const PromoteStudentsPage({super.key});

  @override
  State<PromoteStudentsPage> createState() => _PromoteStudentsPageState();
}

class _PromoteStudentsPageState extends State<PromoteStudentsPage> {
  SchoolClass? _fromClass;
  SchoolClass? _toClass;
  final List<int> _selectedStudents = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ClassProvider>().loadClasses();
      context.read<StudentProvider>().loadStudents();
    });
  }

  List<SchoolClass> _getToClassOptions(List<SchoolClass> allClasses) {
    if (_fromClass == null) return [];
    final fromIndex = allClasses.indexWhere((c) => c.id == _fromClass!.id);
    if (fromIndex >= 0 && fromIndex < allClasses.length - 1) {
      return allClasses.sublist(fromIndex + 1);
    }
    return allClasses;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Promote Students',
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
                  'Select Classes',
                  style: GoogleFonts.montserrat(
                    fontSize: 18,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 16),
                Consumer<ClassProvider>(
                  builder: (context, provider, child) {
                    if (provider.isLoading && provider.classes.isEmpty) {
                      return const Center(child: CircularProgressIndicator());
                    }

                    final allClasses = provider.classes;
                    final toClassOptions = _getToClassOptions(allClasses);

                    return Row(
                      children: [
                        Expanded(child: _buildFromClassDropdown(allClasses)),
                        const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 8),
                          child: Icon(
                            Icons.arrow_forward,
                            color: Color(0xFF6D28D9),
                          ),
                        ),
                        Expanded(child: _buildToClassDropdown(toClassOptions)),
                      ],
                    );
                  },
                ),
                const SizedBox(height: 16),
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
                          'Select students from ${_fromClass?.className ?? 'source class'} to promote to ${_toClass?.className ?? 'target class'}',
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

                final allStudents = provider.students;
                // Filter students by selected "from class"
                final students = _fromClass != null
                    ? allStudents
                          .where((s) => s.currentClassId == _fromClass!.id)
                          .toList()
                    : [];

                if (_fromClass == null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.class_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Please select a "From Class"',
                          style: GoogleFonts.montserrat(
                            fontSize: 16,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  );
                }

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
                          'No students found in ${_fromClass!.className}',
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
                        subtitle: Text(
                          'ID: ${student.admissionNo} | Current: ${student.className ?? 'N/A'} - ${student.sectionName ?? 'N/A'}',
                          style: GoogleFonts.montserrat(fontSize: 12),
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
                  _fromClass == null ||
                  _toClass == null
              ? null
              : _promoteStudents,
          style: ElevatedButton.styleFrom(
            backgroundColor: Colors.green,
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
                  _toClass != null
                      ? 'Promote ${_selectedStudents.length} to ${_toClass!.className}'
                      : 'Select Classes',
                  style: GoogleFonts.montserrat(
                    fontSize: 14,
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

  Widget _buildFromClassDropdown(List<SchoolClass> classes) {
    return DropdownButtonFormField<SchoolClass>(
      decoration: InputDecoration(
        labelText: 'From Class',
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      ),
      initialValue: _fromClass,
      items: classes.map((SchoolClass cls) {
        return DropdownMenuItem<SchoolClass>(
          value: cls,
          child: Text(
            '${cls.className} (${cls.totalStudents})',
            style: GoogleFonts.montserrat(fontSize: 14),
          ),
        );
      }).toList(),
      onChanged: (value) {
        setState(() {
          _fromClass = value;
          _toClass = null;
          _selectedStudents.clear();
        });
      },
      style: GoogleFonts.montserrat(color: Colors.black),
    );
  }

  Widget _buildToClassDropdown(List<SchoolClass> classes) {
    return DropdownButtonFormField<SchoolClass>(
      decoration: InputDecoration(
        labelText: 'To Class',
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      ),
      initialValue: _toClass,
      items: classes.map((SchoolClass cls) {
        return DropdownMenuItem<SchoolClass>(
          value: cls,
          child: Text(
            cls.className,
            style: GoogleFonts.montserrat(fontSize: 14),
          ),
        );
      }).toList(),
      onChanged: (value) {
        setState(() {
          _toClass = value;
        });
      },
      style: GoogleFonts.montserrat(color: Colors.black),
    );
  }

  void _promoteStudents() async {
    if (_fromClass == null || _toClass == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Please select both classes',
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

    final promotionData = {
      'student_ids': _selectedStudents,
      'from_class_id': _fromClass!.id,
      'to_class_id': _toClass!.id,
    };

    final success = await context.read<StudentProvider>().promoteStudents(
      promotionData,
    );

    setState(() {
      _isLoading = false;
    });

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            '${_selectedStudents.length} students promoted successfully!',
            style: GoogleFonts.montserrat(),
          ),
          backgroundColor: Colors.green,
        ),
      );
      setState(() {
        _selectedStudents.clear();
        _fromClass = null;
        _toClass = null;
      });
      // Reload students
      if (mounted) {
        context.read<StudentProvider>().loadStudents();
      }
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Failed to promote students',
            style: GoogleFonts.montserrat(),
          ),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
}
