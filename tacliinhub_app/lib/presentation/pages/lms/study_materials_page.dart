import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/lms_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/student_provider.dart';

class StudyMaterialsPage extends StatefulWidget {
  const StudyMaterialsPage({super.key});

  @override
  State<StudyMaterialsPage> createState() => _StudyMaterialsPageState();
}

class _StudyMaterialsPageState extends State<StudyMaterialsPage> {
  int? _selectedClassId;
  int? _selectedSubjectId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<LmsProvider>();
      provider.loadStudyMaterials(userId: user?.id);

      final studentProvider = context.read<StudentProvider>();
      studentProvider.loadClasses();
    });
  }

  void _loadMaterials() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    final provider = context.read<LmsProvider>();
    provider.loadStudyMaterials(
      userId: user?.id,
      classId: _selectedClassId,
      subjectId: _selectedSubjectId,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Study Materials',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.blue,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () =>
                Navigator.pushNamed(context, '/lms/study-materials/add'),
            tooltip: 'Add Material',
          ),
        ],
      ),
      body: Column(
        children: [
          // Filters
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              children: [
                Consumer<StudentProvider>(
                  builder: (context, studentProvider, child) {
                    return DropdownButtonFormField<int>(
                      decoration: InputDecoration(
                        labelText: 'Class',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 8,
                        ),
                      ),
                      initialValue: _selectedClassId,
                      items: [
                        const DropdownMenuItem<int>(
                          value: null,
                          child: Text('All Classes'),
                        ),
                        ...studentProvider.classes.map((cls) {
                          return DropdownMenuItem<int>(
                            value: cls.id,
                            child: Text(cls.className),
                          );
                        }),
                      ],
                      onChanged: (value) {
                        setState(() {
                          _selectedClassId = value;
                          _selectedSubjectId = null;
                        });
                        _loadMaterials();
                      },
                    );
                  },
                ),
                const SizedBox(height: 12),
                Consumer<LmsProvider>(
                  builder: (context, provider, child) {
                    return DropdownButtonFormField<int>(
                      decoration: InputDecoration(
                        labelText: 'Subject',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 8,
                        ),
                      ),
                      initialValue: _selectedSubjectId,
                      items: [
                        const DropdownMenuItem<int>(
                          value: null,
                          child: Text('All Subjects'),
                        ),
                        // Add subjects dropdown if needed
                      ],
                      onChanged: (value) {
                        setState(() {
                          _selectedSubjectId = value;
                        });
                        _loadMaterials();
                      },
                    );
                  },
                ),
              ],
            ),
          ),
          // Materials List
          Expanded(
            child: Consumer<LmsProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (provider.error != null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.error_outline, size: 48, color: Colors.red),
                        const SizedBox(height: 16),
                        Text(provider.error ?? 'Error loading materials'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadMaterials,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                if (provider.studyMaterials.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.library_books_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No study materials found',
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
                  itemCount: provider.studyMaterials.length,
                  itemBuilder: (context, index) {
                    final material = provider.studyMaterials[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        contentPadding: const EdgeInsets.all(16),
                        leading: CircleAvatar(
                          backgroundColor: Colors.blue.withOpacity(0.1),
                          child: Icon(
                            material.fileType == 'pdf'
                                ? Icons.picture_as_pdf
                                : material.fileType == 'doc' ||
                                      material.fileType == 'docx'
                                ? Icons.description
                                : Icons.insert_drive_file,
                            color: Colors.blue,
                          ),
                        ),
                        title: Text(
                          material.title,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text(material.description),
                            const SizedBox(height: 8),
                            Wrap(
                              spacing: 8,
                              children: [
                                if (material.className != null)
                                  Chip(
                                    label: Text(material.className!),
                                    labelStyle: const TextStyle(fontSize: 12),
                                    padding: EdgeInsets.zero,
                                  ),
                                if (material.subjectName != null)
                                  Chip(
                                    label: Text(material.subjectName!),
                                    labelStyle: const TextStyle(fontSize: 12),
                                    padding: EdgeInsets.zero,
                                  ),
                              ],
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Uploaded: ${DateFormat('MMM d, yyyy').format(DateTime.parse(material.uploadedAt))}',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey[600],
                              ),
                            ),
                          ],
                        ),
                        trailing: material.fileUrl != null
                            ? IconButton(
                                icon: const Icon(Icons.download),
                                onPressed: () {
                                  // Handle download
                                },
                              )
                            : null,
                        onTap: () {
                          // Show material details
                        },
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () =>
            Navigator.pushNamed(context, '/lms/study-materials/add'),
        backgroundColor: Colors.blue,
        child: const Icon(Icons.add),
      ),
    );
  }
}
