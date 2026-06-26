import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/facilities_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class AddVehiclePage extends StatefulWidget {
  const AddVehiclePage({super.key});

  @override
  State<AddVehiclePage> createState() => _AddVehiclePageState();
}

class _AddVehiclePageState extends State<AddVehiclePage> {
  final _formKey = GlobalKey<FormState>();
  final _vehicleNumberController = TextEditingController();
  final _makeController = TextEditingController();
  final _modelController = TextEditingController();
  final _yearController = TextEditingController();
  final _colorController = TextEditingController();
  final _capacityController = TextEditingController();
  final _driverNameController = TextEditingController();
  final _driverPhoneController = TextEditingController();
  final _driverLicenseController = TextEditingController();
  String _selectedVehicleType = 'Bus';
  int? _selectedRouteId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      context.read<FacilitiesProvider>().loadTransportRoutes(userId: user?.id);
    });
  }

  @override
  void dispose() {
    _vehicleNumberController.dispose();
    _makeController.dispose();
    _modelController.dispose();
    _yearController.dispose();
    _colorController.dispose();
    _capacityController.dispose();
    _driverNameController.dispose();
    _driverPhoneController.dispose();
    _driverLicenseController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Add Vehicle',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.orange,
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
                        'Vehicle Information',
                        style: GoogleFonts.montserrat(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 24),
                      TextFormField(
                        controller: _vehicleNumberController,
                        decoration: InputDecoration(
                          labelText: 'Vehicle Number *',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixIcon: const Icon(Icons.confirmation_number),
                        ),
                        validator: (value) => value?.isEmpty ?? true
                            ? 'Please enter vehicle number'
                            : null,
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: DropdownButtonFormField<String>(
                              decoration: InputDecoration(
                                labelText: 'Vehicle Type *',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                              ),
                              initialValue: _selectedVehicleType,
                              items: const [
                                DropdownMenuItem(
                                  value: 'Bus',
                                  child: Text('Bus'),
                                ),
                                DropdownMenuItem(
                                  value: 'Van',
                                  child: Text('Van'),
                                ),
                                DropdownMenuItem(
                                  value: 'Car',
                                  child: Text('Car'),
                                ),
                              ],
                              onChanged: (value) {
                                setState(() {
                                  _selectedVehicleType = value!;
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
                      Row(
                        children: [
                          Expanded(
                            child: TextFormField(
                              controller: _makeController,
                              decoration: InputDecoration(
                                labelText: 'Make',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.business),
                              ),
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: TextFormField(
                              controller: _modelController,
                              decoration: InputDecoration(
                                labelText: 'Model',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.category),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: TextFormField(
                              controller: _yearController,
                              decoration: InputDecoration(
                                labelText: 'Year',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.calendar_today),
                              ),
                              keyboardType: TextInputType.number,
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: TextFormField(
                              controller: _colorController,
                              decoration: InputDecoration(
                                labelText: 'Color',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.color_lens),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 24),
                      Text(
                        'Driver Information',
                        style: GoogleFonts.montserrat(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _driverNameController,
                        decoration: InputDecoration(
                          labelText: 'Driver Name',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixIcon: const Icon(Icons.person),
                        ),
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: TextFormField(
                              controller: _driverPhoneController,
                              decoration: InputDecoration(
                                labelText: 'Driver Phone',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.phone),
                              ),
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: TextFormField(
                              controller: _driverLicenseController,
                              decoration: InputDecoration(
                                labelText: 'Driver License',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.badge),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Consumer<FacilitiesProvider>(
                        builder: (context, provider, child) {
                          if (provider.isLoading) {
                            return DropdownButtonFormField<int>(
                              decoration: InputDecoration(
                                labelText: 'Assign to Route',
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
                              labelText: 'Assign to Route',
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              prefixIcon: const Icon(Icons.route),
                            ),
                            initialValue: _selectedRouteId,
                            items: [
                              const DropdownMenuItem<int>(
                                value: null,
                                child: Text('No Route'),
                              ),
                              if (provider.transportRoutes.isEmpty)
                                const DropdownMenuItem<int>(
                                  value: -1,
                                  enabled: false,
                                  child: Text('No routes available'),
                                )
                              else
                                ...provider.transportRoutes.map((route) {
                                  return DropdownMenuItem<int>(
                                    value: route.id,
                                    child: Text(route.routeName),
                                  );
                                }),
                            ],
                            onChanged: provider.transportRoutes.isEmpty
                                ? null
                                : (value) {
                                    if (value != null && value != -1) {
                                      setState(() {
                                        _selectedRouteId = value;
                                      });
                                    }
                                  },
                          );
                        },
                      ),
                      const SizedBox(height: 24),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: _submitForm,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.orange,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          child: Text(
                            'Add Vehicle',
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

      final success = await provider.addVehicle(
        vehicleNumber: _vehicleNumberController.text,
        vehicleType: _selectedVehicleType,
        make: _makeController.text.isEmpty ? null : _makeController.text,
        model: _modelController.text.isEmpty ? null : _modelController.text,
        year: _yearController.text.isEmpty
            ? null
            : int.tryParse(_yearController.text),
        color: _colorController.text.isEmpty ? null : _colorController.text,
        capacity: int.parse(_capacityController.text),
        driverName: _driverNameController.text.isEmpty
            ? null
            : _driverNameController.text,
        driverPhone: _driverPhoneController.text.isEmpty
            ? null
            : _driverPhoneController.text,
        driverLicense: _driverLicenseController.text.isEmpty
            ? null
            : _driverLicenseController.text,
        routeId: _selectedRouteId,
        userId: user?.id,
      );

      if (mounted) {
        if (success) {
          SweetAlert.showSuccess(
            context: context,
            title: 'Success',
            message: 'Vehicle added successfully!',
          );
          Navigator.pop(context);
        } else {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: provider.error ?? 'Failed to add vehicle',
          );
        }
      }
    }
  }
}
