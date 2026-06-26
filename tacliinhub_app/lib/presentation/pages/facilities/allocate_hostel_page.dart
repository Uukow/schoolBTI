import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/facilities_provider.dart';
import '../../providers/student_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class AllocateHostelPage extends StatefulWidget {
  const AllocateHostelPage({super.key});

  @override
  State<AllocateHostelPage> createState() => _AllocateHostelPageState();
}

class _AllocateHostelPageState extends State<AllocateHostelPage> {
  final _formKey = GlobalKey<FormState>();
  final _rentController = TextEditingController();
  final _remarksController = TextEditingController();
  int? _selectedHostelId;
  int? _selectedRoomId;
  int? _selectedStudentId;
  DateTime _allocationDate = DateTime.now();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<FacilitiesProvider>();
      final studentProvider = context.read<StudentProvider>();
      provider.loadHostels(userId: user?.id);
      provider.loadHostelRooms(userId: user?.id);
      studentProvider.loadClasses();
      studentProvider.loadStudents();
    });
  }

  @override
  void dispose() {
    _rentController.dispose();
    _remarksController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Allocate Hostel',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.purple,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Card(
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Allocation Information',
                        style: GoogleFonts.montserrat(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 24),
                      Consumer<FacilitiesProvider>(
                        builder: (context, provider, child) {
                          if (provider.isLoading && provider.hostels.isEmpty) {
                            return DropdownButtonFormField<int>(
                              decoration: InputDecoration(
                                labelText: 'Select Hostel *',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.hotel),
                              ),
                              initialValue: null,
                              items: const [
                                DropdownMenuItem<int>(
                                  value: null,
                                  enabled: false,
                                  child: Text('Loading...'),
                                ),
                              ],
                              onChanged: null,
                            );
                          }

                          return DropdownButtonFormField<int>(
                            decoration: InputDecoration(
                              labelText: 'Select Hostel *',
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              prefixIcon: const Icon(Icons.hotel),
                            ),
                            initialValue: _selectedHostelId,
                            items: provider.hostels.isEmpty
                                ? [
                                    const DropdownMenuItem<int>(
                                      value: null,
                                      enabled: false,
                                      child: Text('No hostels available'),
                                    ),
                                  ]
                                : provider.hostels.map((hostel) {
                                    return DropdownMenuItem<int>(
                                      value: hostel.id,
                                      child: Text(hostel.name),
                                    );
                                  }).toList(),
                            onChanged: provider.hostels.isEmpty
                                ? null
                                : (value) {
                                    setState(() {
                                      _selectedHostelId = value;
                                      _selectedRoomId =
                                          null; // Reset room selection
                                    });
                                    if (value != null) {
                                      provider.loadHostelRooms(hostelId: value);
                                    }
                                  },
                            validator: (value) =>
                                value == null ? 'Please select hostel' : null,
                          );
                        },
                      ),
                      const SizedBox(height: 16),
                      Consumer<FacilitiesProvider>(
                        builder: (context, provider, child) {
                          final availableRooms = provider.getAvailableRooms(
                            _selectedHostelId ?? 0,
                          );
                          return DropdownButtonFormField<int>(
                            decoration: InputDecoration(
                              labelText: 'Select Room *',
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              prefixIcon: const Icon(Icons.room),
                            ),
                            initialValue: _selectedRoomId,
                            items: availableRooms.isEmpty
                                ? [
                                    const DropdownMenuItem<int>(
                                      value: null,
                                      enabled: false,
                                      child: Text('No rooms available'),
                                    ),
                                  ]
                                : availableRooms.map((room) {
                                    return DropdownMenuItem<int>(
                                      value: room.id,
                                      child: Text(
                                        'Room ${room.roomNumber} (Available: ${room.available})',
                                      ),
                                    );
                                  }).toList(),
                            onChanged: availableRooms.isEmpty
                                ? null
                                : (value) {
                                    setState(() {
                                      _selectedRoomId = value;
                                    });
                                  },
                            validator: (value) =>
                                value == null ? 'Please select room' : null,
                          );
                        },
                      ),
                      const SizedBox(height: 16),
                      Consumer<StudentProvider>(
                        builder: (context, studentProvider, child) {
                          return DropdownButtonFormField<int>(
                            decoration: InputDecoration(
                              labelText: 'Select Student *',
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              prefixIcon: const Icon(Icons.person),
                            ),
                            initialValue: _selectedStudentId,
                            items: studentProvider.students.isEmpty
                                ? [
                                    const DropdownMenuItem<int>(
                                      value: null,
                                      enabled: false,
                                      child: Text('No students available'),
                                    ),
                                  ]
                                : studentProvider.students.map((student) {
                                    final s = student as dynamic;
                                    final name =
                                        '${s?.firstName ?? ''} ${s?.lastName ?? ''}'
                                            .trim();
                                    final admissionNo = s?.admissionNo ?? '';
                                    return DropdownMenuItem<int>(
                                      value: s?.id ?? 0,
                                      child: Text(
                                        name.isEmpty
                                            ? 'Unknown Student'
                                            : '$name${admissionNo.isNotEmpty ? ' ($admissionNo)' : ''}',
                                      ),
                                    );
                                  }).toList(),
                            onChanged: studentProvider.students.isEmpty
                                ? null
                                : (value) {
                                    setState(() {
                                      _selectedStudentId = value;
                                    });
                                  },
                            validator: (value) =>
                                value == null ? 'Please select student' : null,
                          );
                        },
                      ),
                      const SizedBox(height: 16),
                      InkWell(
                        onTap: () async {
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: _allocationDate,
                            firstDate: DateTime.now().subtract(
                              const Duration(days: 365),
                            ),
                            lastDate: DateTime.now(),
                          );
                          if (picked != null) {
                            setState(() {
                              _allocationDate = picked;
                            });
                          }
                        },
                        child: InputDecorator(
                          decoration: InputDecoration(
                            labelText: 'Allocation Date *',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            prefixIcon: const Icon(Icons.calendar_today),
                          ),
                          child: Text(
                            DateFormat('yyyy-MM-dd').format(_allocationDate),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _rentController,
                        decoration: InputDecoration(
                          labelText: 'Monthly Rent',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixText: '\$ ',
                          prefixIcon: const Icon(Icons.attach_money),
                        ),
                        keyboardType: const TextInputType.numberWithOptions(
                          decimal: true,
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _remarksController,
                        decoration: InputDecoration(
                          labelText: 'Remarks',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        maxLines: 3,
                      ),
                      const SizedBox(height: 24),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: _submitForm,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.purple,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          child: Text(
                            'Allocate Hostel',
                            style: GoogleFonts.montserrat(
                              fontSize: 16,
                              fontWeight: FontWeight.w600,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _submitForm() async {
    if (_formKey.currentState!.validate()) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<FacilitiesProvider>();

      final success = await provider.allocateHostel(
        hostelId: _selectedHostelId!,
        roomId: _selectedRoomId!,
        studentId: _selectedStudentId!,
        allocationDate: DateFormat('yyyy-MM-dd').format(_allocationDate),
        monthlyRent: _rentController.text.isEmpty
            ? null
            : double.tryParse(_rentController.text),
        remarks: _remarksController.text.isEmpty
            ? null
            : _remarksController.text,
        userId: user?.id,
      );

      if (mounted) {
        if (success) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'Hostel allocated successfully!',
          );
          Navigator.pop(context);
        } else {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: provider.error ?? 'Failed to allocate hostel',
          );
        }
      }
    }
  }
}
