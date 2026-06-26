import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/facilities_provider.dart';
import '../../providers/auth_provider.dart';

class HostelRoomsPage extends StatefulWidget {
  const HostelRoomsPage({super.key});

  @override
  State<HostelRoomsPage> createState() => _HostelRoomsPageState();
}

class _HostelRoomsPageState extends State<HostelRoomsPage> {
  int? _selectedHostelId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<FacilitiesProvider>();
      provider.loadHostels(userId: user?.id);
      provider.loadHostelRooms(userId: user?.id);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Hostel Rooms',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.indigo,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () =>
                Navigator.pushNamed(context, '/facilities/hostel-rooms/add'),
            tooltip: 'Add Room',
          ),
        ],
      ),
      body: Column(
        children: [
          // Hostel Filter
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: Consumer<FacilitiesProvider>(
              builder: (context, provider, child) {
                return DropdownButtonFormField<int>(
                  decoration: InputDecoration(
                    labelText: 'Filter by Hostel',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    prefixIcon: const Icon(Icons.hotel),
                  ),
                  initialValue: _selectedHostelId,
                  items: [
                    const DropdownMenuItem<int>(
                      value: null,
                      child: Text('All Hostels'),
                    ),
                    ...provider.hostels.map((hostel) {
                      return DropdownMenuItem<int>(
                        value: hostel.id,
                        child: Text(hostel.name),
                      );
                    }),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedHostelId = value;
                    });
                    _loadRooms();
                  },
                );
              },
            ),
          ),

          // Rooms List
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
                        Text(provider.error ?? 'Error loading rooms'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadRooms,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                var rooms = provider.hostelRooms;
                if (_selectedHostelId != null) {
                  rooms = rooms
                      .where((room) => room.hostelId == _selectedHostelId)
                      .toList();
                }

                if (rooms.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.room_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No rooms found',
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
                  itemCount: rooms.length,
                  itemBuilder: (context, index) {
                    final room = rooms[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: room.available > 0
                              ? Colors.green.withOpacity(0.1)
                              : Colors.red.withOpacity(0.1),
                          child: Icon(
                            Icons.room,
                            color: room.available > 0
                                ? Colors.green
                                : Colors.red,
                          ),
                        ),
                        title: Text(
                          'Room ${room.roomNumber}',
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text(room.hostelName),
                            Text('Type: ${room.roomType}'),
                            if (room.rentPerMonth != null)
                              Text(
                                'Rent: \$${room.rentPerMonth!.toStringAsFixed(2)}/month',
                              ),
                            const SizedBox(height: 4),
                            Row(
                              children: [
                                Chip(
                                  label: Text('Capacity: ${room.capacity}'),
                                  backgroundColor: Colors.blue.withOpacity(0.1),
                                  labelStyle: const TextStyle(fontSize: 10),
                                ),
                                const SizedBox(width: 4),
                                Chip(
                                  label: Text('Occupied: ${room.occupied}'),
                                  backgroundColor: Colors.orange.withOpacity(
                                    0.1,
                                  ),
                                  labelStyle: const TextStyle(fontSize: 10),
                                ),
                                const SizedBox(width: 4),
                                Chip(
                                  label: Text('Available: ${room.available}'),
                                  backgroundColor: Colors.green.withOpacity(
                                    0.1,
                                  ),
                                  labelStyle: const TextStyle(fontSize: 10),
                                ),
                              ],
                            ),
                          ],
                        ),
                        trailing: Chip(
                          label: Text(room.status),
                          backgroundColor: room.status == 'Available'
                              ? Colors.green.withOpacity(0.1)
                              : Colors.red.withOpacity(0.1),
                          labelStyle: TextStyle(
                            color: room.status == 'Available'
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
            Navigator.pushNamed(context, '/facilities/hostel-rooms/add'),
        backgroundColor: Colors.indigo,
        child: const Icon(Icons.add),
      ),
    );
  }

  void _loadRooms() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    context.read<FacilitiesProvider>().loadHostelRooms(
      hostelId: _selectedHostelId,
      userId: user?.id,
    );
  }
}
