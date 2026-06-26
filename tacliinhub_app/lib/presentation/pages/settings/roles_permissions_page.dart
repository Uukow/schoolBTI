import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/settings_provider.dart';
import '../../providers/auth_provider.dart';

class RolesPermissionsPage extends StatefulWidget {
  const RolesPermissionsPage({super.key});

  @override
  State<RolesPermissionsPage> createState() => _RolesPermissionsPageState();
}

class _RolesPermissionsPageState extends State<RolesPermissionsPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadRoles();
    });
  }

  void _loadRoles() {
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    final provider = context.read<SettingsProvider>();
    provider.loadRolesAndPermissions(userId: user?.id);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          'Roles & Permissions',
          style: GoogleFonts.montserrat(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.purple,
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
                  Text(provider.error ?? 'Error loading roles'),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: _loadRoles,
                    child: const Text('Retry'),
                  ),
                ],
              ),
            );
          }

          if (provider.roles.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.admin_panel_settings_outlined,
                    size: 64,
                    color: Colors.grey[400],
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'No roles found',
                    style: GoogleFonts.montserrat(color: Colors.grey[600]),
                  ),
                ],
              ),
            );
          }

          return RefreshIndicator(
            onRefresh: () async => _loadRoles(),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: provider.roles.length,
              itemBuilder: (context, index) {
                final role = provider.roles[index];
                final permissionCount = role.permissions.length;
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  elevation: 2,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: ExpansionTile(
                    leading: CircleAvatar(
                      backgroundColor: Colors.purple.withOpacity(0.1),
                      child: Icon(Icons.shield, color: Colors.purple),
                    ),
                    title: Text(
                      role.roleName,
                      style: GoogleFonts.montserrat(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    subtitle: Text(
                      '$permissionCount permission${permissionCount != 1 ? 's' : ''}',
                      style: GoogleFonts.montserrat(),
                    ),
                    children: [
                      if (role.roleDescription != null)
                        Padding(
                          padding: const EdgeInsets.all(16),
                          child: Text(
                            role.roleDescription!,
                            style: GoogleFonts.montserrat(),
                          ),
                        ),
                      if (permissionCount > 0)
                        Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Permissions:',
                                style: GoogleFonts.montserrat(
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Wrap(
                                spacing: 8,
                                runSpacing: 8,
                                children: role.permissions.map((perm) {
                                  return Chip(
                                    label: Text(
                                      perm.permissionName,
                                      style: const TextStyle(fontSize: 12),
                                    ),
                                    backgroundColor: Colors.purple.withOpacity(
                                      0.1,
                                    ),
                                  );
                                }).toList(),
                              ),
                            ],
                          ),
                        ),
                    ],
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
