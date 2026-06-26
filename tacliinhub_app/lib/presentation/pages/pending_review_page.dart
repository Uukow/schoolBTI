import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:awesome_dialog/awesome_dialog.dart';
import '../providers/admission_provider.dart';

class PendingReviewPage extends StatefulWidget {
  const PendingReviewPage({super.key});

  @override
  State<PendingReviewPage> createState() => _PendingReviewPageState();
}

class _PendingReviewPageState extends State<PendingReviewPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AdmissionProvider>().loadAdmissions(status: 'Pending');
    });
  }

  void _showActionDialog(BuildContext context, int admissionId, String action, String studentName) {
    final remarksController = TextEditingController();
    
    AwesomeDialog(
      context: context,
      dialogType: action == 'Approve' ? DialogType.success : DialogType.warning,
      animType: AnimType.scale,
      title: '$action Application',
      desc: 'Are you sure you want to $action $studentName\'s application?',
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            Text(
              'Are you sure you want to $action',
              style: GoogleFonts.montserrat(
                fontSize: 16,
                fontWeight: FontWeight.w600,
              ),
              textAlign: TextAlign.center,
            ),
            Text(
              '$studentName\'s application?',
              style: GoogleFonts.montserrat(
                fontSize: 16,
                fontWeight: FontWeight.w600,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 20),
            TextField(
              controller: remarksController,
              decoration: InputDecoration(
                labelText: 'Remarks (Optional)',
                labelStyle: GoogleFonts.montserrat(),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                contentPadding: const EdgeInsets.all(12),
              ),
              maxLines: 3,
              style: GoogleFonts.montserrat(),
            ),
          ],
        ),
      ),
      btnCancelText: 'Cancel',
      btnCancelOnPress: () {},
      btnOkText: action,
      btnOkColor: action == 'Approve' ? Colors.green : Colors.red,
      btnOkOnPress: () async {
        final provider = context.read<AdmissionProvider>();
        
        final success = action == 'Approve'
            ? await provider.approveAdmission(
                admissionId,
                remarksController.text.isEmpty ? null : remarksController.text,
              )
            : await provider.rejectAdmission(
                admissionId,
                remarksController.text.isEmpty ? null : remarksController.text,
              );

        if (success && context.mounted) {
          AwesomeDialog(
            context: context,
            dialogType: action == 'Approve' ? DialogType.success : DialogType.info,
            animType: AnimType.bottomSlide,
            title: 'Success!',
            desc: 'Application ${action}d successfully!',
            btnOkOnPress: () {},
            btnOkColor: action == 'Approve' ? Colors.green : Colors.red,
          ).show();
        }
      },
    ).show();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Pending Review',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: const Color(0xFF6D28D9),
        elevation: 0,
      ),
      body: Consumer<AdmissionProvider>(
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
                  Icon(Icons.error_outline, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text(
                    provider.error!,
                    style: GoogleFonts.montserrat(
                      fontSize: 16,
                      color: Colors.grey[600],
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () => provider.loadAdmissions(status: 'Pending'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF6D28D9),
                    ),
                    child: Text('Retry', style: GoogleFonts.montserrat()),
                  ),
                ],
              ),
            );
          }

          final admissions = provider.pendingAdmissions;

          if (admissions.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.pending_actions_outlined,
                    size: 64,
                    color: Colors.grey[400],
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'No pending applications',
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
            itemCount: admissions.length,
            itemBuilder: (context, index) {
              final admission = admissions[index];

              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          CircleAvatar(
                            radius: 30,
                            backgroundColor: Colors.orange.withOpacity(0.1),
                            child: Text(
                              admission.firstName[0].toUpperCase(),
                              style: GoogleFonts.montserrat(
                                fontSize: 24,
                                fontWeight: FontWeight.w600,
                                color: Colors.orange,
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  admission.fullName,
                                  style: GoogleFonts.montserrat(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'App No: ${admission.applicationNumber}',
                                  style: GoogleFonts.montserrat(
                                    fontSize: 14,
                                    color: Colors.grey[600],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      const Divider(),
                      const SizedBox(height: 8),
                      _buildInfoRow('Class Applied', admission.classAppliedName ?? 'N/A'),
                      _buildInfoRow('Application Date', admission.applicationDate),
                      _buildInfoRow('Guardian', admission.guardianName ?? 'N/A'),
                      _buildInfoRow('Phone', admission.guardianPhone ?? 'N/A'),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton.icon(
                              onPressed: () {
                                _showActionDialog(context, admission.id, 'Reject', admission.fullName);
                              },
                              icon: const Icon(Icons.close, size: 20),
                              label: Text(
                                'Reject',
                                style: GoogleFonts.montserrat(
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                              style: OutlinedButton.styleFrom(
                                foregroundColor: Colors.red,
                                side: const BorderSide(color: Colors.red, width: 2),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: ElevatedButton.icon(
                              onPressed: () {
                                _showActionDialog(context, admission.id, 'Approve', admission.fullName);
                              },
                              icon: const Icon(Icons.check, size: 20),
                              label: Text(
                                'Approve',
                                style: GoogleFonts.montserrat(
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.green,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                              ),
                            ),
                          ),
                        ],
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

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Text(
            '$label: ',
            style: GoogleFonts.montserrat(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.grey[700],
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: GoogleFonts.montserrat(
                fontSize: 13,
                color: Colors.grey[600],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

