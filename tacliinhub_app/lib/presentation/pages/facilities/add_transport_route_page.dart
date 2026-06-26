import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/facilities_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class AddTransportRoutePage extends StatefulWidget {
  const AddTransportRoutePage({super.key});

  @override
  State<AddTransportRoutePage> createState() => _AddTransportRoutePageState();
}

class _AddTransportRoutePageState extends State<AddTransportRoutePage> {
  final _formKey = GlobalKey<FormState>();
  final _routeNameController = TextEditingController();
  final _routeCodeController = TextEditingController();
  final _startLocationController = TextEditingController();
  final _endLocationController = TextEditingController();
  final _distanceController = TextEditingController();
  final _fareController = TextEditingController();
  final _descriptionController = TextEditingController();

  @override
  void dispose() {
    _routeNameController.dispose();
    _routeCodeController.dispose();
    _startLocationController.dispose();
    _endLocationController.dispose();
    _distanceController.dispose();
    _fareController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Add Transport Route',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.green,
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
                        'Route Information',
                        style: GoogleFonts.montserrat(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 24),
                      Row(
                        children: [
                          Expanded(
                            flex: 2,
                            child: TextFormField(
                              controller: _routeNameController,
                              decoration: InputDecoration(
                                labelText: 'Route Name *',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.route),
                              ),
                              validator: (value) =>
                                  value?.isEmpty ?? true ? 'Please enter route name' : null,
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: TextFormField(
                              controller: _routeCodeController,
                              decoration: InputDecoration(
                                labelText: 'Route Code',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.qr_code),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _startLocationController,
                        decoration: InputDecoration(
                          labelText: 'Start Location *',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixIcon: const Icon(Icons.location_on),
                        ),
                        validator: (value) =>
                            value?.isEmpty ?? true ? 'Please enter start location' : null,
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _endLocationController,
                        decoration: InputDecoration(
                          labelText: 'End Location *',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          prefixIcon: const Icon(Icons.location_on),
                        ),
                        validator: (value) =>
                            value?.isEmpty ?? true ? 'Please enter end location' : null,
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: TextFormField(
                              controller: _distanceController,
                              decoration: InputDecoration(
                                labelText: 'Distance (km)',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixIcon: const Icon(Icons.straighten),
                              ),
                              keyboardType: const TextInputType.numberWithOptions(decimal: true),
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: TextFormField(
                              controller: _fareController,
                              decoration: InputDecoration(
                                labelText: 'Fare',
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                prefixText: '\$ ',
                                prefixIcon: const Icon(Icons.attach_money),
                              ),
                              keyboardType: const TextInputType.numberWithOptions(decimal: true),
                            ),
                          ),
                        ],
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
                            backgroundColor: Colors.green,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          child: Text(
                            'Add Route',
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

      final success = await provider.addTransportRoute(
        routeName: _routeNameController.text,
        routeCode: _routeCodeController.text.isEmpty
            ? null
            : _routeCodeController.text,
        startLocation: _startLocationController.text,
        endLocation: _endLocationController.text,
        distance: _distanceController.text.isEmpty
            ? null
            : double.tryParse(_distanceController.text),
        fare: _fareController.text.isEmpty
            ? null
            : double.tryParse(_fareController.text),
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
            message: 'Route added successfully!',
          );
          Navigator.pop(context);
        } else {
          SweetAlert.showError(
            context: context,
            title: 'Error',
            message: provider.error ?? 'Failed to add route',
          );
        }
      }
    }
  }
}
