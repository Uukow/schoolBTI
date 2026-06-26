import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/academic_provider.dart';
import 'add_syllabus_page.dart';

class SyllabusPage extends StatefulWidget {
  const SyllabusPage({super.key});

  @override
  State<SyllabusPage> createState() => _SyllabusPageState();
}

class _SyllabusPageState extends State<SyllabusPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AcademicProvider>().loadSyllabus();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Syllabus',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.indigo,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () async {
              final result = await Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const AddSyllabusPage()),
              );
              if (result == true) {
                context.read<AcademicProvider>().loadSyllabus();
              }
            },
            tooltip: 'Add Syllabus',
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const AddSyllabusPage()),
          );
          if (result == true) {
            context.read<AcademicProvider>().loadSyllabus();
          }
        },
        backgroundColor: Colors.indigo,
        tooltip: 'Add Syllabus',
        child: const Icon(Icons.add),
      ),
      body: Consumer<AcademicProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 48, color: Colors.red),
                  const SizedBox(height: 16),
                  Text(provider.error ?? 'Error loading syllabus'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () => provider.loadSyllabus(),
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          if (provider.syllabus.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.description, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text(
                    'No syllabus found',
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
            itemCount: provider.syllabus.length,
            itemBuilder: (context, index) {
              final syllabus = provider.syllabus[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                child: ListTile(
                  leading: CircleAvatar(
                    backgroundColor: Colors.indigo.withOpacity(0.1),
                    child: Icon(Icons.description, color: Colors.indigo[700]),
                  ),
                  title: Text(
                    syllabus.title,
                    style: GoogleFonts.montserrat(fontWeight: FontWeight.w600),
                  ),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const SizedBox(height: 4),
                      Text(
                        '${syllabus.className ?? "N/A"} - ${syllabus.subjectName}',
                        style: GoogleFonts.montserrat(fontSize: 12),
                      ),
                      if (syllabus.fileName != null)
                        Text(
                          'File: ${syllabus.fileName}',
                          style: GoogleFonts.montserrat(fontSize: 12),
                        ),
                    ],
                  ),
                  trailing: syllabus.filePath != null
                      ? IconButton(
                          icon: const Icon(Icons.download),
                          onPressed: () {
                            // TODO: Implement file download
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text('Download: ${syllabus.fileName}'),
                              ),
                            );
                          },
                        )
                      : null,
                ),
              );
            },
          );
        },
      ),
    );
  }
}
