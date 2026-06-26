import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/settings_provider.dart';
import '../../providers/auth_provider.dart';
import '../../../core/sweet_alert.dart';

class GranularPermissionsPage extends StatefulWidget {
  const GranularPermissionsPage({super.key});

  @override
  State<GranularPermissionsPage> createState() =>
      _GranularPermissionsPageState();
}

class _GranularPermissionsPageState extends State<GranularPermissionsPage> {
  int? _selectedRoleId;
  final Map<int, bool> _selectedPermissions = {};

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  void _loadData() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    final provider = context.read<SettingsProvider>();
    provider.loadRolesAndPermissions(userId: user?.id);
  }

  void _onRoleSelected(int? roleId) {
    setState(() {
      _selectedRoleId = roleId;
      _selectedPermissions.clear();

      if (roleId != null) {
        final provider = context.read<SettingsProvider>();
        final role = provider.roles.firstWhere((r) => r.id == roleId);
        for (var perm in role.permissions) {
          _selectedPermissions[perm.id] = true;
        }
      }
    });
  }

  Future<void> _savePermissions() async {
    if (_selectedRoleId == null) {
      SweetAlert.showError(
        context: context,
        title: 'Error',
        message: 'Please select a role',
      );
      return;
    }

    final user = Provider.of<AuthProvider>(context, listen: false).user;
    if (user == null) {
      SweetAlert.showError(
        context: context,
        title: 'Error',
        message: 'User not logged in',
      );
      return;
    }

    final permissionIds = _selectedPermissions.entries
        .where((e) => e.value)
        .map((e) => e.key)
        .toList();

    final provider = context.read<SettingsProvider>();
    final success = await provider.saveRolePermissions(
      _selectedRoleId!,
      permissionIds,
      userId: user.id,
    );

    if (mounted) {
      if (success) {
        SweetAlert.showSuccess(
          context: context,
          title: 'Success',
          message: 'Permissions saved successfully',
        );
      } else {
        SweetAlert.showError(
          context: context,
          title: 'Error',
          message: provider.error ?? 'Failed to save permissions',
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Granular Permissions',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.red,
        elevation: 0,
      ),
      body: Consumer<SettingsProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.roles.isEmpty) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.error != null && provider.roles.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.error_outline, size: 48, color: Colors.red),
                  const SizedBox(height: 16),
                  Text(provider.error ?? 'Error loading permissions'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: _loadData,
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          // Group permissions by module
          final Map<String, List<dynamic>> groupedPermissions = {};
          for (var perm in provider.permissions) {
            if (!groupedPermissions.containsKey(perm.module)) {
              groupedPermissions[perm.module] = [];
            }
            groupedPermissions[perm.module]!.add(perm);
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                DropdownButtonFormField<int>(
                  decoration: InputDecoration(
                    labelText: 'Select Role',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  initialValue: _selectedRoleId,
                  items: provider.roles.map((role) {
                    return DropdownMenuItem<int>(
                      value: role.id,
                      child: Text(role.roleName),
                    );
                  }).toList(),
                  onChanged: _onRoleSelected,
                ),
                const SizedBox(height: 24),
                if (_selectedRoleId != null) ...[
                  ...groupedPermissions.entries.map((entry) {
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          entry.key,
                          style: GoogleFonts.montserrat(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 12),
                        ...entry.value.map((perm) {
                          return CheckboxListTile(
                            title: Text(perm.permissionName),
                            subtitle: perm.description != null
                                ? Text(perm.description!)
                                : null,
                            value: _selectedPermissions[perm.id] ?? false,
                            onChanged: (value) {
                              setState(() {
                                _selectedPermissions[perm.id] = value ?? false;
                              });
                            },
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                          );
                        }),
                        const SizedBox(height: 16),
                      ],
                    );
                  }),
                  const SizedBox(height: 24),
                  ElevatedButton(
                    onPressed: provider.isLoading ? null : _savePermissions,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: provider.isLoading
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation<Color>(
                                Colors.white,
                              ),
                            ),
                          )
                        : Text(
                            'Save Permissions',
                            style: GoogleFonts.montserrat(
                              fontWeight: FontWeight.w600,
                              color: Colors.white,
                            ),
                          ),
                  ),
                ] else
                  Center(
                    child: Padding(
                      padding: const EdgeInsets.all(32),
                      child: Column(
                        children: [
                          Icon(
                            Icons.info_outline,
                            size: 64,
                            color: Colors.grey[400],
                          ),
                          const SizedBox(height: 16),
                          Text(
                            'Select a role to manage permissions',
                            style: GoogleFonts.montserrat(
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
              ],
            ),
          );
        },
      ),
    );
  }
}
