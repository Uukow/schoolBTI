import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/facilities_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class AddVehicleMaintenancePage extends StatefulWidget {
  const AddVehicleMaintenancePage({super.key});

  @override
  State<AddVehicleMaintenancePage> createState() =>
      _AddVehicleMaintenancePageState();
}

class _AddVehicleMaintenancePageState extends State<AddVehicleMaintenancePage> {
  final _formKey = GlobalKey<FormState>();
  final _costController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _serviceProviderController = TextEditingController();
  final _odometerReadingController = TextEditingController();
  final _nextMaintenanceDateController = TextEditingController();
  int? _selectedVehicleId;
  String _selectedMaintenanceType = 'Regular Service';
  DateTime _maintenanceDate = DateTime.now();
  DateTime? _nextMaintenanceDate;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      context.read<FacilitiesProvider>().loadVehicles(userId: user?.id);
    });
  }

  @override
  void dispose() {
    _costController.dispose();
    _descriptionController.dispose();
    _serviceProviderController.dispose();
    _odometerReadingController.dispose();
    _nextMaintenanceDateController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Add Vehicle Maintenance',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.brown,
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
                        'Maintenance Information',
                        style: GoogleFonts.montserrat(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 24),
                      Consumer<FacilitiesProvider>(
                        builder: (context, provider, child) {
                          if (provider.isLoading && provider.vehicles.isEmpty) {
                            return DropdownButtonFormField<int>(
                              decoration: InputDecoration(
                                labelText: 'Select Vehicle *',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.directions_bus),
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
                              labelText: 'Select Vehicle *',
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              prefixIcon: const Icon(Icons.directions_bus),
                            ),
                            initialValue: _selectedVehicleId,
                            items: provider.vehicles.isEmpty
                                ? [
                                    const DropdownMenuItem<int>(
                                      value: null,
                                      enabled: false,
                                      child: Text('No vehicles available'),
                                    ),
                                  ]
                                : provider.vehicles.map((vehicle) {
                                    return DropdownMenuItem<int>(
                                      value: vehicle.id,
                                      child: Text(
                                        '${vehicle.vehicleNumber} (${vehicle.vehicleType})',
                                      ),
                                    );
                                  }).toList(),
                            onChanged: provider.vehicles.isEmpty
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
                      DropdownButtonFormField<String>(
                        decoration: InputDecoration(
                          labelText: 'Maintenance Type *',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixIcon: const Icon(Icons.build),
                        ),
                        initialValue: _selectedMaintenanceType,
                        items: const [
                          DropdownMenuItem(
                            value: 'Regular Service',
                            child: Text('Regular Service'),
                          ),
                          DropdownMenuItem(
                            value: 'Repair',
                            child: Text('Repair'),
                          ),
                          DropdownMenuItem(
                            value: 'Inspection',
                            child: Text('Inspection'),
                          ),
                          DropdownMenuItem(
                            value: 'Tire Replacement',
                            child: Text('Tire Replacement'),
                          ),
                          DropdownMenuItem(
                            value: 'Oil Change',
                            child: Text('Oil Change'),
                          ),
                          DropdownMenuItem(
                            value: 'Other',
                            child: Text('Other'),
                          ),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _selectedMaintenanceType = value!;
                          });
                        },
                      ),
                      const SizedBox(height: 16),
                      InkWell(
                        onTap: () async {
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: _maintenanceDate,
                            firstDate: DateTime.now().subtract(
                              const Duration(days: 365),
                            ),
                            lastDate: DateTime.now(),
                          );
                          if (picked != null) {
                            setState(() {
                              _maintenanceDate = picked;
                            });
                          }
                        },
                        child: InputDecorator(
                          decoration: InputDecoration(
                            labelText: 'Maintenance Date *',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            prefixIcon: const Icon(Icons.calendar_today),
                          ),
                          child: Text(
                            DateFormat('yyyy-MM-dd').format(_maintenanceDate),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _costController,
                        decoration: InputDecoration(
                          labelText: 'Cost *',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixText: '\$ ',
                          prefixIcon: const Icon(Icons.attach_money),
                        ),
                        keyboardType: const TextInputType.numberWithOptions(
                          decimal: true,
                        ),
                        validator: (value) {
                          if (value?.isEmpty ?? true) {
                            return 'Please enter cost';
                          }
                          final cost = double.tryParse(value!);
                          if (cost == null || cost < 0) {
                            return 'Invalid cost';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _odometerReadingController,
                        decoration: InputDecoration(
                          labelText: 'Odometer Reading (km)',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixIcon: const Icon(Icons.speed),
                        ),
                        keyboardType: TextInputType.number,
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _serviceProviderController,
                        decoration: InputDecoration(
                          labelText: 'Service Provider',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixIcon: const Icon(Icons.business),
                        ),
                      ),
                      const SizedBox(height: 16),
                      InkWell(
                        onTap: () async {
                          final picked = await showDatePicker(
                            context: context,
                            initialDate:
                                _nextMaintenanceDate ??
                                DateTime.now().add(const Duration(days: 30)),
                            firstDate: DateTime.now(),
                            lastDate: DateTime.now().add(
                              const Duration(days: 365),
                            ),
                          );
                          if (picked != null) {
                            setState(() {
                              _nextMaintenanceDate = picked;
                              _nextMaintenanceDateController.text = DateFormat(
                                'yyyy-MM-dd',
                              ).format(picked);
                            });
                          }
                        },
                        child: InputDecorator(
                          decoration: InputDecoration(
                            labelText: 'Next Maintenance Date',
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            prefixIcon: const Icon(Icons.event),
                          ),
                          child: Text(
                            _nextMaintenanceDate == null
                                ? 'Select date'
                                : DateFormat(
                                    'yyyy-MM-dd',
                                  ).format(_nextMaintenanceDate!),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _descriptionController,
                        decoration: InputDecoration(
                          labelText: 'Description',
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
                            backgroundColor: Colors.brown,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          child: Text(
                            'Add Maintenance Record',
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

      final success = await provider.addVehicleMaintenance(
        vehicleId: _selectedVehicleId!,
        maintenanceType: _selectedMaintenanceType,
        maintenanceDate: DateFormat('yyyy-MM-dd').format(_maintenanceDate),
        cost: double.parse(_costController.text),
        odometerReading: _odometerReadingController.text.isEmpty
            ? null
            : int.tryParse(_odometerReadingController.text),
        serviceProvider: _serviceProviderController.text.isEmpty
            ? null
            : _serviceProviderController.text,
        nextMaintenanceDate: _nextMaintenanceDate == null
            ? null
            : DateFormat('yyyy-MM-dd').format(_nextMaintenanceDate!),
        description: _descriptionController.text.isEmpty
            ? null
            : _descriptionController.text,
        userId: user?.id,
      );

      if (mounted) {
        if (success) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'Maintenance record added successfully!',
          );
          Navigator.pop(context);
        } else {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: provider.error ?? 'Failed to add maintenance record',
          );
        }
      }
    }
  }
}
