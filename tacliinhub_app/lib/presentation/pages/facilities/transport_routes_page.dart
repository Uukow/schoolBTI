import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/facilities_provider.dart';
import '../../providers/auth_provider.dart';

class TransportRoutesPage extends StatefulWidget {
  const TransportRoutesPage({super.key});

  @override
  State<TransportRoutesPage> createState() => _TransportRoutesPageState();
}

class _TransportRoutesPageState extends State<TransportRoutesPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      context.read<FacilitiesProvider>().loadTransportRoutes(userId: user?.id);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Transport Routes',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.green,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => Navigator.pushNamed(context, '/facilities/transport-routes/add'),
            tooltip: 'Add Route',
          ),
        ],
      ),
      body: Consumer<FacilitiesProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 48, color: Colors.red),
                  const SizedBox(height: 16),
                  Text(provider.error ?? 'Error loading routes'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () {
                      final user = Provider.of<AuthProvider>(context, listen: false).user;
                      provider.loadTransportRoutes(userId: user?.id);
                    },
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          if (provider.transportRoutes.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.route_outlined, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text(
                    'No routes found',
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
            itemCount: provider.transportRoutes.length,
            itemBuilder: (context, index) {
              final route = provider.transportRoutes[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                child: ListTile(
                  leading: CircleAvatar(
                    backgroundColor: Colors.green.withOpacity(0.1),
                    child: const Icon(Icons.route, color: Colors.green),
                  ),
                  title: Text(
                    route.routeName,
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const SizedBox(height: 4),
                      Text('${route.startLocation} → ${route.endLocation}'),
                      if (route.distance != null)
                        Text('Distance: ${route.distance!.toStringAsFixed(2)} km'),
                      if (route.fare != null)
                        Text(
                          'Fare: \$${route.fare!.toStringAsFixed(2)}',
                          style: GoogleFonts.montserrat(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                    ],
                  ),
                  trailing: Chip(
                    label: Text(route.status),
                    backgroundColor: route.status == 'Active'
                        ? Colors.green.withOpacity(0.1)
                        : Colors.grey.withOpacity(0.1),
                    labelStyle: TextStyle(
                      color: route.status == 'Active' ? Colors.green : Colors.grey,
                      fontSize: 10,
                    ),
                  ),
                ),
              );
            },
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.pushNamed(context, '/facilities/transport-routes/add'),
        backgroundColor: Colors.green,
        child: const Icon(Icons.add),
      ),
    );
  }
}
