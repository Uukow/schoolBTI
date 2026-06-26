import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../providers/facilities_provider.dart';
import '../../providers/auth_provider.dart';

class TransportAssignmentsPage extends StatefulWidget {
  const TransportAssignmentsPage({super.key});

  @override
  State<TransportAssignmentsPage> createState() =>
      _TransportAssignmentsPageState();
}

class _TransportAssignmentsPageState extends State<TransportAssignmentsPage> {
  int? _selectedRouteId;
  String? _selectedStatus;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      final provider = context.read<FacilitiesProvider>();
      provider.loadTransportRoutes(userId: user?.id);
      provider.loadVehicles(userId: user?.id);
      provider.loadTransportAssignments(userId: user?.id);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Transport Assignments',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.red,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => Navigator.pushNamed(
              context,
              '/facilities/transport-assignments/assign',
            ),
            tooltip: 'Assign Transport',
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
                        _loadAssignments();
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
                    _loadAssignments();
                  },
                ),
              ],
            ),
          ),

          // Assignments List
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
                        Text(provider.error ?? 'Error loading assignments'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadAssignments,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                var assignments = provider.transportAssignments;
                if (_selectedRouteId != null) {
                  assignments = assignments
                      .where((assign) => assign.routeId == _selectedRouteId)
                      .toList();
                }
                if (_selectedStatus != null) {
                  assignments = assignments
                      .where((assign) => assign.status == _selectedStatus)
                      .toList();
                }

                if (assignments.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.assignment_outlined,
                          size: 64,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No assignments found',
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
                  itemCount: assignments.length,
                  itemBuilder: (context, index) {
                    final assignment = assignments[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: assignment.status == 'Active'
                              ? Colors.green.withOpacity(0.1)
                              : Colors.grey.withOpacity(0.1),
                          child: Icon(
                            Icons.directions_bus,
                            color: assignment.status == 'Active'
                                ? Colors.green
                                : Colors.grey,
                          ),
                        ),
                        title: Text(
                          assignment.studentName,
                          style: GoogleFonts.montserrat(
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 4),
                            Text('Route: ${assignment.routeName}'),
                            Text('Vehicle: ${assignment.vehicleNumber}'),
                            if (assignment.pickupPoint != null)
                              Text('Pickup: ${assignment.pickupPoint}'),
                            if (assignment.dropPoint != null)
                              Text('Drop: ${assignment.dropPoint}'),
                            Text(
                              'Assigned: ${DateFormat('MMM d, yyyy').format(DateTime.parse(assignment.assignmentDate))}',
                              style: GoogleFonts.montserrat(fontSize: 12),
                            ),
                            if (assignment.monthlyFee != null)
                              Text(
                                'Fee: \$${assignment.monthlyFee!.toStringAsFixed(2)}/month',
                                style: GoogleFonts.montserrat(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                          ],
                        ),
                        trailing: Chip(
                          label: Text(assignment.status),
                          backgroundColor: assignment.status == 'Active'
                              ? Colors.green.withOpacity(0.1)
                              : Colors.grey.withOpacity(0.1),
                          labelStyle: TextStyle(
                            color: assignment.status == 'Active'
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
          '/facilities/transport-assignments/assign',
        ),
        backgroundColor: Colors.red,
        child: const Icon(Icons.add),
      ),
    );
  }

  void _loadAssignments() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    context.read<FacilitiesProvider>().loadTransportAssignments(
      routeId: _selectedRouteId,
      status: _selectedStatus,
      userId: user?.id,
    );
  }
}
