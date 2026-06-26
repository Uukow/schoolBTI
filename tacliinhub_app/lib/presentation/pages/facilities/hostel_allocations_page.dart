import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/facilities_provider.dart';
import '../../providers/auth_provider.dart';

class HostelAllocationsPage extends StatefulWidget {
  const HostelAllocationsPage({super.key});

  @override
  State<HostelAllocationsPage> createState() => _HostelAllocationsPageState();
}

class _HostelAllocationsPageState extends State<HostelAllocationsPage> {
  int? _selectedHostelId;
  String? _selectedStatus;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<FacilitiesProvider>();
      provider.loadHostels(userId: user?.id);
      provider.loadHostelAllocations(userId: user?.id);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Hostel Allocations',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.purple,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => Navigator.pushNamed(
              context,
              '/facilities/hostel-allocations/allocate',
            ),
            tooltip: 'Allocate Hostel',
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
                        _loadAllocations();
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
                    DropdownMenuItem(value: 'Active', child: Text('Active')),
                    DropdownMenuItem(
                      value: 'Completed',
                      child: Text('Completed'),
                    ),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedStatus = value;
                    });
                    _loadAllocations();
                  },
                ),
              ],
            ),
          ),

          // Allocations List
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
                        Text(provider.error ?? 'Error loading allocations'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadAllocations,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                var allocations = provider.hostelAllocations;
                if (_selectedHostelId != null) {
                  allocations = allocations
                      .where((alloc) => alloc.hostelId == _selectedHostelId)
                      .toList();
                }
                if (_selectedStatus != null) {
                  allocations = allocations
                      .where((alloc) => alloc.status == _selectedStatus)
                      .toList();
                }

                if (allocations.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.assignment_ind_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No allocations found',
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
                  itemCount: allocations.length,
                  itemBuilder: (context, index) {
                    final allocation = allocations[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: allocation.status == 'Active'
                              ? Colors.green.withOpacity(0.1)
                              : Colors.grey.withOpacity(0.1),
                          child: Icon(
                            Icons.assignment_ind,
                            color: allocation.status == 'Active'
                                ? Colors.green
                                : Colors.grey,
                          ),
                        ),
                        title: Text(
                          allocation.studentName,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text(
                              '${allocation.hostelName} - Room ${allocation.roomNumber}',
                            ),
                            Text(
                              'Allocated: ${DateFormat('MMM d, yyyy').format(DateTime.parse(allocation.allocationDate))}',
                              style: GoogleFonts.montserrat(fontSize: 12),
                            ),
                            if (allocation.deallocationDate != null)
                              Text(
                                'Deallocated: ${DateFormat('MMM d, yyyy').format(DateTime.parse(allocation.deallocationDate!))}',
                                style: GoogleFonts.montserrat(fontSize: 12),
                              ),
                            if (allocation.monthlyRent != null)
                              Text(
                                'Rent: \$${allocation.monthlyRent!.toStringAsFixed(2)}/month',
                                style: GoogleFonts.montserrat(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                          ],
                        ),
                        trailing: Chip(
                          label: Text(allocation.status),
                          backgroundColor: allocation.status == 'Active'
                              ? Colors.green.withOpacity(0.1)
                              : Colors.grey.withOpacity(0.1),
                          labelStyle: TextStyle(
                            color: allocation.status == 'Active'
                                ? Colors.green
                                : Colors.grey,
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
        onPressed: () => Navigator.pushNamed(
          context,
          '/facilities/hostel-allocations/allocate',
        ),
        backgroundColor: Colors.purple,
        child: const Icon(Icons.add),
      ),
    );
  }

  void _loadAllocations() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    context.read<FacilitiesProvider>().loadHostelAllocations(
      hostelId: _selectedHostelId,
      status: _selectedStatus,
      userId: user?.id,
    );
  }
}
