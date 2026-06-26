import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:tacliinhub_app/core/constants.dart';
import '../providers/auth_provider.dart';
import '../providers/classes_provider.dart';
import '../widgets/branch_selector.dart';

class ClassesPage extends StatefulWidget {
  const ClassesPage({super.key});

  @override
  State<ClassesPage> createState() => _ClassesPageState();
}

class _ClassesPageState extends State<ClassesPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user != null) {
        Provider.of<ClassesProvider>(context, listen: false).loadClasses(user.id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).user;

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Classes'),
      ),
      body: Column(
        children: [
          // Branch Selector (for Super Admin)
          const BranchSelector(),
          // Classes List
          Expanded(
            child: Consumer<ClassesProvider>(
              builder: (context, classesProvider, child) {
                if (classesProvider.isLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (classesProvider.error != null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.error_outline, size: 48, color: Colors.red),
                        const SizedBox(height: 16),
                        Text('Error: ${classesProvider.error}'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: () {
                            if (user != null) {
                              classesProvider.loadClasses(user.id);
                            }
                          },
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                final data = classesProvider.data;
                
                if (data == null || (data['subjects'] as List).isEmpty) {
                  return const Center(child: Text('No subjects assigned yet.'));
                }

                final subjects = data['subjects'] as List;

                return ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: subjects.length,
                  itemBuilder: (context, index) {
                    final subject = subjects[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      child: ListTile(
                        contentPadding: const EdgeInsets.all(16),
                        leading: CircleAvatar(
                          backgroundColor: AppConstants.primaryColor.withOpacity(0.1),
                          child: Text(
                            subject['subject_code'].toString().substring(0, 2),
                            style: const TextStyle(color: AppConstants.primaryColor, fontWeight: FontWeight.bold),
                          ),
                        ),
                        title: Text(
                          subject['subject_name'],
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Row(
                              children: [
                                const Icon(Icons.person, size: 14, color: Colors.grey),
                                const SizedBox(width: 4),
                                Text('Teacher: ${subject['teacher_name']}'),
                              ],
                            ),
                            const SizedBox(height: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: subject['subject_type'] == 'Core' ? Colors.blue.withOpacity(0.1) : Colors.green.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                subject['subject_type'],
                                style: TextStyle(
                                  fontSize: 12,
                                  color: subject['subject_type'] == 'Core' ? Colors.blue : Colors.green,
                                ),
                              ),
                            )
                          ],
                        ),
                        onTap: () {
                          // Navigate to details if needed
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
    );
  }
}
