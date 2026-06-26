import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/facilities_provider.dart';
import '../../providers/auth_provider.dart';

class HostelsPage extends StatefulWidget {
  const HostelsPage({super.key});

  @override
  State<HostelsPage> createState() => _HostelsPageState();
}

class _HostelsPageState extends State<HostelsPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      context.read<FacilitiesProvider>().loadHostels(userId: user?.id);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Hostels',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.blue,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => Navigator.pushNamed(context, '/facilities/hostels/add'),
            tooltip: 'Add Hostel',
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
                  Text(provider.error ?? 'Error loading hostels'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () {
                      final user = Provider.of<AuthProvider>(context, listen: false).user;
                      provider.loadHostels(userId: user?.id);
                    },
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          if (provider.hostels.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.hotel_outlined, size: 64, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text(
                    'No hostels found',
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
            itemCount: provider.hostels.length,
            itemBuilder: (context, index) {
              final hostel = provider.hostels[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                child: ListTile(
                  leading: CircleAvatar(
                    backgroundColor: Colors.blue.withOpacity(0.1),
                    child: const Icon(Icons.hotel, color: Colors.blue),
                  ),
                  title: Text(
                    hostel.name,
                    style: GoogleFonts.montserrat(
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const SizedBox(height: 4),
                      if (hostel.address != null) Text('Address: ${hostel.address}'),
                      if (hostel.contactPerson != null)
                        Text('Contact: ${hostel.contactPerson}'),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Chip(
                            label: Text('Rooms: ${hostel.totalRooms}'),
                            backgroundColor: Colors.blue.withOpacity(0.1),
                            labelStyle: const TextStyle(fontSize: 10),
                          ),
                          const SizedBox(width: 4),
                          Chip(
                            label: Text('Available: ${hostel.availableRooms}'),
                            backgroundColor: Colors.green.withOpacity(0.1),
                            labelStyle: const TextStyle(fontSize: 10),
                          ),
                          const SizedBox(width: 4),
                          Chip(
                            label: Text('Capacity: ${hostel.totalCapacity}'),
                            backgroundColor: Colors.orange.withOpacity(0.1),
                            labelStyle: const TextStyle(fontSize: 10),
                          ),
                        ],
                      ),
                    ],
                  ),
                  trailing: Chip(
                    label: Text(hostel.status),
                    backgroundColor: hostel.status == 'Active'
                        ? Colors.green.withOpacity(0.1)
                        : Colors.grey.withOpacity(0.1),
                    labelStyle: TextStyle(
                      color: hostel.status == 'Active' ? Colors.green : Colors.grey,
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
        onPressed: () => Navigator.pushNamed(context, '/facilities/hostels/add'),
        backgroundColor: Colors.blue,
        child: const Icon(Icons.add),
      ),
    );
  }
}

