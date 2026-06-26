import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/marks_provider.dart';

class MarksPage extends StatefulWidget {
  const MarksPage({super.key});

  @override
  State<MarksPage> createState() => _MarksPageState();
}

class _MarksPageState extends State<MarksPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      if (user != null) {
        Provider.of<MarksProvider>(context, listen: false).loadMarks(user.id);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).user;

    return Scaffold(
      appBar: AppBar(title: const Text('My Results')),
      body: Consumer<MarksProvider>(
        builder: (context, marksProvider, child) {
          if (marksProvider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          if (marksProvider.error != null) {
            return Center(child: Text('Error: ${marksProvider.error}'));
          }

          final marks = marksProvider.marks;

          if (marks == null || marks.isEmpty) {
            return const Center(child: Text('No marks available.'));
          }

          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: marks.length,
            itemBuilder: (context, index) {
              final mark = marks[index];
              final score = mark['obtained_marks'];
              final total = mark['total_marks'];
              final percentage = mark['percentage'];

              Color scoreColor = Colors.green;
              if (percentage < 35) {
                scoreColor = Colors.red;
              } else if (percentage < 60)
                scoreColor = Colors.orange;

              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              mark['exam_name'],
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                              ),
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color: scoreColor.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              '$score / $total',
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                color: scoreColor,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        '${mark['subject_name']} (${mark['subject_code']}) - ${mark['exam_type']}',
                        style: TextStyle(color: Colors.grey[700]),
                      ),
                      const SizedBox(height: 8),
                      LinearProgressIndicator(
                        value: percentage / 100,
                        backgroundColor: Colors.grey[200],
                        valueColor: AlwaysStoppedAnimation<Color>(scoreColor),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Percentage: $percentage%',
                        style: const TextStyle(
                          fontSize: 12,
                          color: Colors.grey,
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}
