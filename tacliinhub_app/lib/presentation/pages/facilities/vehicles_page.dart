import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/facilities_provider.dart';
import '../../providers/auth_provider.dart';

class VehiclesPage extends StatefulWidget {
  const VehiclesPage({super.key});

  @override
  State<VehiclesPage> createState() => _VehiclesPageState();
}

class _VehiclesPageState extends State<VehiclesPage> {
  int? _selectedRouteId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<FacilitiesProvider>();
      provider.loadTransportRoutes(userId: user?.id);
      provider.loadVehicles(userId: user?.id);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Vehicles',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.orange,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () =>
                Navigator.pushNamed(context, '/facilities/vehicles/add'),
            tooltip: 'Add Vehicle',
          ),
        ],
      ),
      body: Column(
        children: [
          // Route Filter
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Consumer<FacilitiesProvider>(
              builder: (context, provider, child) {
                return DropdownButtonFormField<int>(
                  decoration: InputDecoration(
                    labelText: 'Filter by Route',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    prefixIcon: const Icon(Icons.route),
                  ),
                  initialValue: _selectedRouteId,
                  items: [
                    const DropdownMenuItem<int>(
                      value: null,
                      child: Text('All Routes'),
                    ),
                    ...provider.transportRoutes.map((route) {
                      return DropdownMenuItem<int>(
                        value: route.id,
                        child: Text(route.routeName),
                      );
                    }),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedRouteId = value;
                    });
                    _loadVehicles();
                  },
                );
              },
            ),
          ),

          // Vehicles List
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
                        Text(provider.error ?? 'Error loading vehicles'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadVehicles,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                var vehicles = provider.vehicles;
                if (_selectedRouteId != null) {
                  vehicles = vehicles
                      .where((vehicle) => vehicle.routeId == _selectedRouteId)
                      .toList();
                }

                if (vehicles.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.directions_bus_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No vehicles found',
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
                  itemCount: vehicles.length,
                  itemBuilder: (context, index) {
                    final vehicle = vehicles[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: Colors.orange.withOpacity(0.1),
                          child: Icon(
                            _getVehicleIcon(vehicle.vehicleType),
                            color: Colors.orange,
                          ),
                        ),
                        title: Text(
                          vehicle.vehicleNumber,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text('Type: ${vehicle.vehicleType}'),
                            if (vehicle.make != null && vehicle.model != null)
                              Text('${vehicle.make} ${vehicle.model}'),
                            if (vehicle.driverName != null)
                              Text('Driver: ${vehicle.driverName}'),
                            if (vehicle.routeName != null)
                              Text('Route: ${vehicle.routeName}'),
                            const SizedBox(height: 4),
                            Chip(
                              label: Text('Capacity: ${vehicle.capacity}'),
                              backgroundColor: Colors.blue.withOpacity(0.1),
                              labelStyle: const TextStyle(fontSize: 10),
                            ),
                          ],
                        ),
                        trailing: Chip(
                          label: Text(vehicle.status),
                          backgroundColor: vehicle.status == 'Active'
                              ? Colors.green.withOpacity(0.1)
                              : Colors.red.withOpacity(0.1),
                          labelStyle: TextStyle(
                            color: vehicle.status == 'Active'
                                ? Colors.green
                                : Colors.red,
                            fontSize: 10,
                          ),
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
            Navigator.pushNamed(context, '/facilities/vehicles/add'),
        backgroundColor: Colors.orange,
        child: const Icon(Icons.add),
      ),
    );
  }

  IconData _getVehicleIcon(String type) {
    switch (type.toLowerCase()) {
      case 'bus':
        return Icons.directions_bus;
      case 'van':
        return Icons.airport_shuttle;
      case 'car':
        return Icons.directions_car;
      default:
        return Icons.directions_bus;
    }
  }

  void _loadVehicles() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    context.read<FacilitiesProvider>().loadVehicles(
      routeId: _selectedRouteId,
      userId: user?.id,
    );
  }
}
