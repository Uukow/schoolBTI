import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/facilities_provider.dart';
import '../../providers/student_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class AssignTransportPage extends StatefulWidget {
  const AssignTransportPage({super.key});

  @override
  State<AssignTransportPage> createState() => _AssignTransportPageState();
}

class _AssignTransportPageState extends State<AssignTransportPage> {
  final _formKey = GlobalKey<FormState>();
  final _monthlyFeeController = TextEditingController();
  final _pickupPointController = TextEditingController();
  final _dropPointController = TextEditingController();
  final _remarksController = TextEditingController();
  int? _selectedRouteId;
  int? _selectedVehicleId;
  int? _selectedStudentId;
  DateTime _assignmentDate = DateTime.now();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<FacilitiesProvider>();
      final studentProvider = context.read<StudentProvider>();
      provider.loadTransportRoutes(userId: user?.id);
      provider.loadVehicles(userId: user?.id);
      studentProvider.loadClasses();
      studentProvider.loadStudents();
    });
  }

  @override
  void dispose() {
    _monthlyFeeController.dispose();
    _pickupPointController.dispose();
    _dropPointController.dispose();
    _remarksController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Assign Transport',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.red,
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
                        'Assignment Information',
                        style: GoogleFonts.montserrat(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 24),
                      Consumer<FacilitiesProvider>(
                        builder: (context, provider, child) {
                          if (provider.isLoading &&
                              provider.transportRoutes.isEmpty) {
                            return DropdownButtonFormField<int>(
                              decoration: InputDecoration(
                                labelText: 'Select Route *',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.route),
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
                              labelText: 'Select Route *',
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              prefixIcon: const Icon(Icons.route),
                            ),
                            initialValue: _selectedRouteId,
                            items: provider.transportRoutes.isEmpty
                                ? [
                                    const DropdownMenuItem<int>(
                                      value: null,
                                      enabled: false,
                                      child: Text('No routes available'),
                                    ),
                                  ]
                                : provider.transportRoutes.map((route) {
                                    return DropdownMenuItem<int>(
                                      value: route.id,
                                      child: Text(
                                        '${route.routeName} (${route.startLocation} → ${route.endLocation})',
                                      ),
                                    );
                                  }).toList(),
                            onChanged: provider.transportRoutes.isEmpty
                                ? null
                                : (value) {
                                    setState(() {
                                      _selectedRouteId = value;
                                      _selectedVehicleId =
                                          null; // Reset vehicle selection
                                    });
                                    if (value != null) {
                                      provider.loadVehicles(routeId: value);
                                    }
                                  },
                            validator: (value) =>
                                value == null ? 'Please select route' : null,
                          );
                        },
                      ),
                      const SizedBox(height: 16),
                      Consumer<FacilitiesProvider>(
                        builder: (context, provider, child) {
                          final availableVehicles = provider
                              .getAvailableVehicles(_selectedRouteId);
                          return DropdownButtonFormField<int>(
                            decoration: InputDecoration(
                              labelText: 'Select Vehicle *',
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              prefixIcon: const Icon(Icons.directions_bus),
                            ),
                            initialValue: _selectedVehicleId,
                            items: availableVehicles.isEmpty
                                ? [
                                    const DropdownMenuItem<int>(
                                      value: null,
                                      enabled: false,
                                      child: Text('No vehicles available'),
                                    ),
                                  ]
                                : availableVehicles.map((vehicle) {
                                    return DropdownMenuItem<int>(
                                      value: vehicle.id,
                                      child: Text(
                                        '${vehicle.vehicleNumber} (${vehicle.vehicleType}, Capacity: ${vehicle.capacity})',
                                      ),
                                    );
                                  }).toList(),
                            onChanged: availableVehicles.isEmpty
                                ? null
                                : (value) {
                                    setState(() {
                                      _selectedVehicleId = value;
                                    });
                                  },
                            validator: (value) =>
                                value == null ? 'Please select vehicle' : null,
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
                            initialDate: _assignmentDate,
                            firstDate: DateTime.now().subtract(
                              const Duration(days: 365),
                            ),
                            lastDate: DateTime.now(),
                          );
                          if (picked != null) {
                            setState(() {
                              _assignmentDate = picked;
                            });
                          }
                        },
                        child: InputDecorator(
                          decoration: InputDecoration(
                            labelText: 'Assignment Date *',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            prefixIcon: const Icon(Icons.calendar_today),
                          ),
                          child: Text(
                            DateFormat('yyyy-MM-dd').format(_assignmentDate),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: TextFormField(
                              controller: _pickupPointController,
                              decoration: InputDecoration(
                                labelText: 'Pickup Point',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.location_on),
                              ),
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: TextFormField(
                              controller: _dropPointController,
                              decoration: InputDecoration(
                                labelText: 'Drop Point',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.location_on),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _monthlyFeeController,
                        decoration: InputDecoration(
                          labelText: 'Monthly Fee',
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
                            backgroundColor: Colors.red,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          child: Text(
                            'Assign Transport',
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

      final success = await provider.assignTransport(
        routeId: _selectedRouteId!,
        vehicleId: _selectedVehicleId!,
        studentId: _selectedStudentId!,
        assignmentDate: DateFormat('yyyy-MM-dd').format(_assignmentDate),
        monthlyFee: _monthlyFeeController.text.isEmpty
            ? null
            : double.tryParse(_monthlyFeeController.text),
        pickupPoint: _pickupPointController.text.isEmpty
            ? null
            : _pickupPointController.text,
        dropPoint: _dropPointController.text.isEmpty
            ? null
            : _dropPointController.text,
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
            message: 'Transport assigned successfully!',
          );
          Navigator.pop(context);
        } else {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: provider.error ?? 'Failed to assign transport',
          );
        }
      }
    }
  }
}
