import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/facilities_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class AddHostelRoomPage extends StatefulWidget {
  const AddHostelRoomPage({super.key});

  @override
  State<AddHostelRoomPage> createState() => _AddHostelRoomPageState();
}

class _AddHostelRoomPageState extends State<AddHostelRoomPage> {
  final _formKey = GlobalKey<FormState>();
  final _roomNumberController = TextEditingController();
  final _capacityController = TextEditingController();
  final _rentController = TextEditingController();
  final _facilitiesController = TextEditingController();
  int? _selectedHostelId;
  String _selectedRoomType = 'Single';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      context.read<FacilitiesProvider>().loadHostels(userId: user?.id);
    });
  }

  @override
  void dispose() {
    _roomNumberController.dispose();
    _capacityController.dispose();
    _rentController.dispose();
    _facilitiesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Add Hostel Room',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.indigo,
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
                        'Room Information',
                        style: GoogleFonts.montserrat(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 24),
                      Consumer<FacilitiesProvider>(
                        builder: (context, provider, child) {
                          if (provider.isLoading) {
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
                                    });
                                  },
                            validator: (value) =>
                                value == null ? 'Please select hostel' : null,
                          );
                        },
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _roomNumberController,
                        decoration: InputDecoration(
                          labelText: 'Room Number *',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixIcon: const Icon(Icons.room),
                        ),
                        validator: (value) => value?.isEmpty ?? true
                            ? 'Please enter room number'
                            : null,
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: DropdownButtonFormField<String>(
                              decoration: InputDecoration(
                                labelText: 'Room Type *',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                              ),
                              initialValue: _selectedRoomType,
                              items: const [
                                DropdownMenuItem(
                                  value: 'Single',
                                  child: Text('Single'),
                                ),
                                DropdownMenuItem(
                                  value: 'Double',
                                  child: Text('Double'),
                                ),
                                DropdownMenuItem(
                                  value: 'Triple',
                                  child: Text('Triple'),
                                ),
                                DropdownMenuItem(
                                  value: 'Dormitory',
                                  child: Text('Dormitory'),
                                ),
                              ],
                              onChanged: (value) {
                                setState(() {
                                  _selectedRoomType = value!;
                                });
                              },
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: TextFormField(
                              controller: _capacityController,
                              decoration: InputDecoration(
                                labelText: 'Capacity *',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.people),
                              ),
                              keyboardType: TextInputType.number,
                              validator: (value) {
                                if (value?.isEmpty ?? true) {
                                  return 'Required';
                                }
                                final capacity = int.tryParse(value!);
                                if (capacity == null || capacity <= 0) {
                                  return 'Invalid';
                                }
                                return null;
                              },
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _rentController,
                        decoration: InputDecoration(
                          labelText: 'Rent per Month',
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
                        controller: _facilitiesController,
                        decoration: InputDecoration(
                          labelText: 'Facilities (comma-separated)',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          hintText: 'e.g., AC, WiFi, TV',
                        ),
                      ),
                      const SizedBox(height: 24),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: _submitForm,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.indigo,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          child: Text(
                            'Add Room',
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

      final success = await provider.addHostelRoom(
        hostelId: _selectedHostelId!,
        roomNumber: _roomNumberController.text,
        roomType: _selectedRoomType,
        capacity: int.parse(_capacityController.text),
        rentPerMonth: _rentController.text.isEmpty
            ? null
            : double.tryParse(_rentController.text),
        facilities: _facilitiesController.text.isEmpty
            ? null
            : _facilitiesController.text,
        userId: user?.id,
      );

      if (mounted) {
        if (success) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'Room added successfully!',
          );
          Navigator.pop(context);
        } else {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: provider.error ?? 'Failed to add room',
          );
        }
      }
    }
  }
}
