import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/hr_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/branch_filter_provider.dart';
import '../../../core/branch_helper.dart';
import '../../widgets/branch_selector.dart';

class StaffPage extends StatefulWidget {
  const StaffPage({super.key});

  @override
  State<StaffPage> createState() => _StaffPageState();
}

class _StaffPageState extends State<StaffPage> {
  String? _selectedStatus;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = Provider.of<AuthProvider>(context, listen: false).user;
      context.read<HrProvider>().loadStaff(userId: user?.id);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Staff Management',
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
            onPressed: () => Navigator.pushNamed(context, '/hr/staff/add'),
            tooltip: 'Add Staff',
          ),
        ],
      ),
      body: Column(
        children: [
          // Branch Selector (for Super Admin)
          const BranchSelector(),
          // Filter
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.white,
            child: DropdownButtonFormField<String>(
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
                DropdownMenuItem(value: 'Inactive', child: Text('Inactive')),
                DropdownMenuItem(value: 'Resigned', child: Text('Resigned')),
                DropdownMenuItem(
                  value: 'Terminated',
                  child: Text('Terminated'),
                ),
              ],
              onChanged: (value) {
                setState(() {
                  _selectedStatus = value;
                });
                _loadStaff();
              },
            ),
          ),

          // Staff List
          Expanded(
            child: Consumer<BranchFilterProvider>(
              builder: (context, branchProvider, child) {
                // Reload staff when branch changes
                WidgetsBinding.instance.addPostFrameCallback((_) {
                  if (mounted) {
                    _loadStaff();
                  }
                });

                return Consumer<HrProvider>(
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
                            Text(provider.error ?? 'Error loading staff'),
                            const SizedBox(height: 16),
                            ElevatedButton(
                              onPressed: _loadStaff,
                              child: const Text('Retry'),
                            ),
                          ],
                        ),
                      );
                    }

                    var staffList = provider.staff;
                    if (_selectedStatus != null) {
                      staffList = staffList
                          .where((s) => s.status == _selectedStatus)
                          .toList();
                    }

                    if (staffList.isEmpty) {
                      return Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.people_outline,
                              size: 64,
                              color: Colors.grey[400],
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'No staff found',
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
                      itemCount: staffList.length,
                      itemBuilder: (context, index) {
                        final staff = staffList[index];
                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          elevation: 2,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: ListTile(
                            leading: CircleAvatar(
                              backgroundColor: staff.status == 'Active'
                                  ? Colors.green.withOpacity(0.1)
                                  : Colors.grey.withOpacity(0.1),
                              child: Icon(
                                Icons.person,
                                color: staff.status == 'Active'
                                    ? Colors.green
                                    : Colors.grey,
                              ),
                            ),
                            title: Text(
                              staff.fullName,
                              style: GoogleFonts.montserrat(
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            subtitle: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const SizedBox(height: 4),
                                Text('ID: ${staff.staffId}'),
                                Text('Designation: ${staff.designation}'),
                                if (staff.department != null)
                                  Text('Department: ${staff.department}'),
                                Text('Phone: ${staff.phone}'),
                                if (staff.email != null)
                                  Text('Email: ${staff.email}'),
                              ],
                            ),
                            trailing: Chip(
                              label: Text(staff.status),
                              backgroundColor: _getStatusColor(
                                staff.status,
                              ).withOpacity(0.1),
                              labelStyle: TextStyle(
                                color: _getStatusColor(staff.status),
                                fontSize: 10,
                              ),
                            ),
                          ),
                        );
                      },
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => Navigator.pushNamed(context, '/hr/staff/add'),
        backgroundColor: Colors.blue,
        child: const Icon(Icons.add),
      ),
    );
  }

  void _loadStaff() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    final branchId = BranchHelper.getBranchId(context);
    context.read<HrProvider>().loadStaff(
      userId: user?.id,
      status: _selectedStatus,
      branchId: branchId,
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'Active':
        return Colors.green;
      case 'Inactive':
        return Colors.orange;
      case 'Resigned':
        return Colors.blue;
      case 'Terminated':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}
