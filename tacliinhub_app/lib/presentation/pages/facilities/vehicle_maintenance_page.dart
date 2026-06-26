import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/facilities_provider.dart';
import '../../providers/auth_provider.dart';

class VehicleMaintenancePage extends StatefulWidget {
  const VehicleMaintenancePage({super.key});

  @override
  State<VehicleMaintenancePage> createState() => _VehicleMaintenancePageState();
}

class _VehicleMaintenancePageState extends State<VehicleMaintenancePage> {
  int? _selectedVehicleId;
  String? _selectedStatus;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<FacilitiesProvider>();
      provider.loadVehicles(userId: user?.id);
      provider.loadVehicleMaintenance(userId: user?.id);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Vehicle Maintenance',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.brown,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => Navigator.pushNamed(
              context,
              '/facilities/vehicle-maintenance/add',
            ),
            tooltip: 'Add Maintenance',
          ),
        ],
      ),
      body: Column(
        children: [
          // Filters
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Column(
              children: [
                Consumer<FacilitiesProvider>(
                  builder: (context, provider, child) {
                    return DropdownButtonFormField<int>(
                      decoration: InputDecoration(
                        labelText: 'Filter by Vehicle',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        prefixIcon: const Icon(Icons.directions_bus),
                      ),
                      initialValue: _selectedVehicleId,
                      items: [
                        const DropdownMenuItem<int>(
                          value: null,
                          child: Text('All Vehicles'),
                        ),
                        ...provider.vehicles.map((vehicle) {
                          return DropdownMenuItem<int>(
                            value: vehicle.id,
                            child: Text(
                              '${vehicle.vehicleNumber} (${vehicle.vehicleType})',
                            ),
                          );
                        }),
                      ],
                      onChanged: (value) {
                        setState(() {
                          _selectedVehicleId = value;
                        });
                        _loadMaintenance();
                      },
                    );
                  },
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<String>(
                  decoration: InputDecoration(
                    labelText: 'Filter by Status',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    prefixIcon: const Icon(Icons.filter_list),
                  ),
                  initialValue: _selectedStatus,
                  items: const [
                    DropdownMenuItem<String>(
                      value: null,
                      child: Text('All Statuses'),
                    ),
                    DropdownMenuItem(
                      value: 'Completed',
                      child: Text('Completed'),
                    ),
                    DropdownMenuItem(value: 'Pending', child: Text('Pending')),
                    DropdownMenuItem(
                      value: 'In Progress',
                      child: Text('In Progress'),
                    ),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedStatus = value;
                    });
                    _loadMaintenance();
                  },
                ),
              ],
            ),
          ),

          // Maintenance List
          Expanded(
            child: Consumer<FacilitiesProvider>(
              builder: (context, provider, child) {
                if (provider.isLoading) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (provider.error != null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.error_outline,
                          size: 48,
                          color: Colors.red,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          provider.error ?? 'Error loading maintenance records',
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadMaintenance,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                var maintenance = provider.vehicleMaintenance;
                if (_selectedVehicleId != null) {
                  maintenance = maintenance
                      .where((m) => m.vehicleId == _selectedVehicleId)
                      .toList();
                }
                if (_selectedStatus != null) {
                  maintenance = maintenance
                      .where((m) => m.status == _selectedStatus)
                      .toList();
                }

                if (maintenance.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.build_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No maintenance records found',
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
                  itemCount: maintenance.length,
                  itemBuilder: (context, index) {
                    final record = maintenance[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: Colors.brown.withOpacity(0.1),
                          child: const Icon(Icons.build, color: Colors.brown),
                        ),
                        title: Text(
                          record.vehicleNumber,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text('Type: ${record.maintenanceType}'),
                            Text(
                              'Date: ${DateFormat('MMM d, yyyy').format(DateTime.parse(record.maintenanceDate))}',
                              style: GoogleFonts.montserrat(fontSize: 12),
                            ),
                            if (record.description != null)
                              Text(
                                record.description!,
                                style: GoogleFonts.montserrat(fontSize: 12),
                              ),
                            if (record.serviceProvider != null)
                              Text(
                                'Service Provider: ${record.serviceProvider}',
                                style: GoogleFonts.montserrat(fontSize: 12),
                              ),
                            if (record.odometerReading != null)
                              Text(
                                'Odometer: ${record.odometerReading} km',
                                style: GoogleFonts.montserrat(fontSize: 12),
                              ),
                            if (record.nextMaintenanceDate != null)
                              Text(
                                'Next: ${DateFormat('MMM d, yyyy').format(DateTime.parse(record.nextMaintenanceDate!))}',
                                style: GoogleFonts.montserrat(
                                  fontSize: 12,
                                  color: Colors.blue,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                          ],
                        ),
                        trailing: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text(
                              '\$${record.cost.toStringAsFixed(2)}',
                              style: GoogleFonts.montserrat(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: Colors.brown[700],
                              ),
                            ),
                            Chip(
                              label: Text(record.status),
                              backgroundColor: _getStatusColor(
                                record.status,
                              ).withOpacity(0.1),
                              labelStyle: TextStyle(
                                color: _getStatusColor(record.status),
                                fontSize: 10,
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
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () =>
            Navigator.pushNamed(context, '/facilities/vehicle-maintenance/add'),
        backgroundColor: Colors.brown,
        child: const Icon(Icons.add),
      ),
    );
  }

  void _loadMaintenance() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    context.read<FacilitiesProvider>().loadVehicleMaintenance(
      vehicleId: _selectedVehicleId,
      status: _selectedStatus,
      userId: user?.id,
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'completed':
        return Colors.green;
      case 'pending':
        return Colors.orange;
      case 'in progress':
        return Colors.blue;
      default:
        return Colors.grey;
    }
  }
}
