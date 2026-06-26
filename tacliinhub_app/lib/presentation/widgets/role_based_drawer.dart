import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/permissions_provider.dart';
import '../../core/constants.dart';
import '../pages/login_page.dart';
import 'app_logo.dart';

class RoleBasedDrawer extends StatelessWidget {
  const RoleBasedDrawer({super.key});

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final permissionsProvider = Provider.of<PermissionsProvider>(context);
    final user = authProvider.user;

    // Debug: Print user role
    if (user != null) {
      print('🔍 User Role: ${user.role}');
    }

    return Drawer(
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.fromLTRB(16, 40, 16, 16),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [AppConstants.primaryColor, AppConstants.primaryColor],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            child: Column(
              children: [
                // Logo
                Container(
                  constraints: const BoxConstraints(maxWidth: 250),
                  child: const AppLogo.iconLight(
                    height: 50,
                    showText: true,
                    text: '',
                  ),
                ),
                const SizedBox(height: 20),
                // User Info
                Row(
                  children: [
                    CircleAvatar(
                      radius: 30,
                      backgroundColor: Colors.white,
                      backgroundImage:
                          user?.profileImage != null &&
                              user!.profileImage!.isNotEmpty
                          ? NetworkImage(
                              '${AppConstants.baseUrl}/${user.profileImage}',
                            )
                          : null,
                      child:
                          user?.profileImage == null ||
                              (user?.profileImage?.isEmpty ?? true)
                          ? Text(
                              (user?.fullName != null &&
                                      user!.fullName.isNotEmpty)
                                  ? user.fullName[0].toUpperCase()
                                  : (user?.username != null &&
                                        user!.username.isNotEmpty)
                                  ? user.username[0].toUpperCase()
                                  : 'U',
                              style: const TextStyle(
                                fontSize: 24,
                                color: AppConstants.primaryColor,
                                fontWeight: FontWeight.bold,
                              ),
                            )
                          : null,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            user?.fullName ?? 'User',
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          if (user?.email != null && user!.email.isNotEmpty)
                            Text(
                              user.email,
                              style: TextStyle(
                                color: Colors.white.withOpacity(0.9),
                                fontSize: 12,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          Expanded(
            child: ListView(
              padding: EdgeInsets.zero,
              children: _buildMenuItems(context, user, permissionsProvider),
            ),
          ),
          ListTile(
            leading: const Icon(Icons.logout, color: Colors.red),
            title: const Text('Logout', style: TextStyle(color: Colors.red)),
            onTap: () async {
              await authProvider.logout();
              if (context.mounted) {
                Navigator.pushAndRemoveUntil(
                  context,
                  MaterialPageRoute(builder: (context) => const LoginPage()),
                  (route) => false,
                );
              }
            },
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }

  List<Widget> _buildMenuItems(
    BuildContext context,
    user,
    PermissionsProvider permissionsProvider,
  ) {
    final menuItems = <Widget>[];

    // Dashboard - Role-based
    if (user?.role == 'Teacher') {
      menuItems.add(
        _buildDrawerItem(Icons.dashboard, 'Teacher Dashboard', () {
          Navigator.pop(context);
          Navigator.pushReplacementNamed(context, '/teacher/dashboard');
        }),
      );
    } else if (user?.role == 'Student') {
      menuItems.add(
        _buildDrawerItem(Icons.dashboard, 'Student Dashboard', () {
          Navigator.pop(context);
          Navigator.pushReplacementNamed(context, '/student/dashboard');
        }),
      );
    } else {
      menuItems.add(
        _buildDrawerItem(Icons.dashboard, 'Dashboard', () {
          Navigator.pop(context);
          Navigator.pushReplacementNamed(context, '/dashboard');
        }),
      );
    }

    menuItems.add(const Divider());

    // Student Portal Menu Items (only for students)
    if (user?.role == 'Student') {
      menuItems.addAll(
        _buildStudentMenuItems(context, permissionsProvider, user),
      );
      menuItems.add(const Divider());
    }

    // Teacher Portal Menu Items (only for teachers) - Strictly ordered as per requirements
    if (user?.role == 'Teacher') {
      menuItems.addAll(
        _buildTeacherMenuItems(context, permissionsProvider, user),
      );
      menuItems.add(const Divider());
    }

    // Module-based menu items (filtered by permissions)
    if (_canAccess(permissionsProvider, 'academics', user)) {
      menuItems.add(
        _buildDrawerItem(Icons.school, 'Academics', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/academics');
        }),
      );
    }

    if (_canAccess(permissionsProvider, 'examinations', user)) {
      menuItems.add(
        _buildDrawerItem(Icons.assignment, 'Examinations', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/examinations');
        }),
      );
    }

    if (_canAccess(permissionsProvider, 'fees', user)) {
      menuItems.add(
        _buildDrawerItem(Icons.account_balance, 'Fees & Finance', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/fees');
        }),
      );
    }

    if (_canAccess(permissionsProvider, 'library', user)) {
      menuItems.add(
        _buildDrawerItem(Icons.library_books, 'Library', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/library');
        }),
      );
    }

    if (_canAccess(permissionsProvider, 'facilities', user)) {
      menuItems.add(
        _buildDrawerItem(Icons.business, 'Facilities', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/facilities');
        }),
      );
    }

    if (_canAccess(permissionsProvider, 'hr', user)) {
      menuItems.add(
        _buildDrawerItem(Icons.people, 'HR & Payroll', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/hr');
        }),
      );
    }

    if (_canAccess(permissionsProvider, 'lms', user)) {
      menuItems.add(
        _buildDrawerItem(Icons.school_outlined, 'LMS', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/lms');
        }),
      );
    }

    if (_canAccess(permissionsProvider, 'communication', user)) {
      menuItems.add(
        _buildDrawerItem(Icons.message, 'Communication', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/communication');
        }),
      );
    }

    if (_canAccess(permissionsProvider, 'events', user)) {
      menuItems.add(
        _buildDrawerItem(Icons.event, 'Events & Calendar', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/events');
        }),
      );
    }

    if (_canAccess(permissionsProvider, 'support', user)) {
      menuItems.add(
        _buildDrawerItem(Icons.support_agent, 'Support Tickets', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/support');
        }),
      );
    }

    if (_canAccess(permissionsProvider, 'reports', user)) {
      menuItems.add(
        _buildDrawerItem(Icons.assessment, 'Reports', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/reports');
        }),
      );
    }

    // Attendance (for non-teachers, teachers use teacher portal)
    if (user?.role != 'Teacher' &&
        _canAccess(permissionsProvider, 'attendance', user)) {
      menuItems.add(
        _buildDrawerItem(Icons.how_to_reg, 'Attendance', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/attendance/dashboard');
        }),
      );
    }

    // Admin/Staff only items
    if (user?.role != 'Teacher' && user?.role != 'Student') {
      menuItems.add(const Divider());

      if (_canAccess(permissionsProvider, 'students', user)) {
        menuItems.add(
          _buildDrawerItem(Icons.school, 'Students', () {
            Navigator.pop(context);
            Navigator.pushNamed(context, '/students');
          }),
        );
      }

      if (_canAccess(permissionsProvider, 'admissions', user)) {
        menuItems.add(
          _buildDrawerItem(Icons.app_registration, 'Admissions', () {
            Navigator.pop(context);
            Navigator.pushNamed(context, '/admissions');
          }),
        );
      }

      if (_canAccess(permissionsProvider, 'facilities', user) &&
          (user?.role == 'Super Admin' || user?.role == 'Admin')) {
        menuItems.add(
          _buildDrawerItem(Icons.business, 'Branches', () {
            Navigator.pop(context);
            Navigator.pushNamed(context, '/branches');
          }),
        );
      }
    }

    // Common items (only for non-teachers, teachers have profile in teacher portal)
    if (user?.role != 'Teacher') {
      menuItems.add(const Divider());
      menuItems.add(
        _buildDrawerItem(Icons.person, 'Profile', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/profile');
        }),
      );
    }

    if (_canAccess(permissionsProvider, 'settings', user)) {
      menuItems.add(
        _buildDrawerItem(Icons.settings, 'Settings', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/settings');
        }),
      );
    }

    return menuItems;
  }

  bool _canAccess(
    PermissionsProvider permissionsProvider,
    String module,
    user,
  ) {
    // If permissions are not loaded yet, use role-based fallback
    if (permissionsProvider.permissions == null) {
      return _roleBasedAccess(module, user?.role);
    }

    // Check permissions first
    if (permissionsProvider.canAccessModule(module)) {
      return true;
    }

    // Fallback to role-based access
    return _roleBasedAccess(module, user?.role);
  }

  bool _roleBasedAccess(String module, String? role) {
    if (role == null) return false;

    // Super Admin has access to everything
    if (role == 'Super Admin') return true;

    // Admin has access to most modules
    if (role == 'Admin') {
      return [
        'dashboard',
        'students',
        'admissions',
        'academics',
        'attendance',
        'examinations',
        'fees',
        'library',
        'facilities',
        'hr',
        'lms',
        'communication',
        'events',
        'reports',
        'support',
      ].contains(module);
    }

    // Teacher has limited access
    if (role == 'Teacher') {
      return [
        'dashboard',
        'teacher_portal',
        'academics',
        'attendance',
        'examinations',
        'library',
        'lms',
        'communication',
        'events',
        'support',
      ].contains(module);
    }

    // Default: no access
    return false;
  }

  /// Builds teacher menu items in the exact order specified by requirements
  /// All items must filter data by teacher assignment
  List<Widget> _buildTeacherMenuItems(
    BuildContext context,
    PermissionsProvider permissionsProvider,
    user,
  ) {
    final items = <Widget>[];

    // 1. Dashboard (already added above, but ensure it's in teacher portal)
    // 2. My Classes
    items.add(
      _buildDrawerItem(Icons.class_, 'My Classes', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/teacher/classes');
      }),
    );

    // 3. My Students
    items.add(
      _buildDrawerItem(Icons.people, 'My Students', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/teacher/students');
      }),
    );

    // 4. Student Reports
    if (_canAccess(permissionsProvider, 'reports', user)) {
      items.add(
        _buildDrawerItem(Icons.assessment, 'Student Reports', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/student-reports');
        }),
      );
    }

    // 5. My Timetable
    items.add(
      _buildDrawerItem(Icons.schedule, 'My Timetable', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/teacher/timetable');
      }),
    );

    // 6. Mark Attendance
    items.add(
      _buildDrawerItem(Icons.how_to_reg, 'Mark Attendance', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/teacher/attendance');
      }),
    );

    // 7. Reports
    if (_canAccess(permissionsProvider, 'reports', user)) {
      items.add(
        _buildDrawerItem(Icons.bar_chart, 'Reports', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/reports');
        }),
      );
    }

    // 8. Enter Marks
    items.add(
      _buildDrawerItem(Icons.assignment, 'Enter Marks', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/teacher/marks');
      }),
    );

    // 9. Results
    if (_canAccess(permissionsProvider, 'examinations', user)) {
      items.add(
        _buildDrawerItem(Icons.emoji_events, 'Results', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/results');
        }),
      );
    }

    // 10. Report Cards
    if (_canAccess(permissionsProvider, 'examinations', user)) {
      items.add(
        _buildDrawerItem(Icons.description, 'Report Cards', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/report-cards');
        }),
      );
    }

    // 11. Analytics
    if (_canAccess(permissionsProvider, 'examinations', user)) {
      items.add(
        _buildDrawerItem(Icons.analytics, 'Analytics', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/analytics');
        }),
      );
    }

    // 12. Lesson Plans
    items.add(
      _buildDrawerItem(Icons.book, 'Lesson Plans', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/teacher/lesson-plans');
      }),
    );

    // 13. Books
    if (_canAccess(permissionsProvider, 'library', user)) {
      items.add(
        _buildDrawerItem(Icons.menu_book, 'Books', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/books');
        }),
      );
    }

    // 14. Library Resources
    if (_canAccess(permissionsProvider, 'library', user)) {
      items.add(
        _buildDrawerItem(Icons.library_books, 'Library Resources', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/library-resources');
        }),
      );
    }

    // 15. Issue History
    if (_canAccess(permissionsProvider, 'library', user)) {
      items.add(
        _buildDrawerItem(Icons.history, 'Issue History', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/issue-history');
        }),
      );
    }

    // 16. Leave Management
    if (_canAccess(permissionsProvider, 'hr', user)) {
      items.add(
        _buildDrawerItem(Icons.event_busy, 'Leave Management', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/leave-management');
        }),
      );
    }

    // 17. LMS
    if (_canAccess(permissionsProvider, 'lms', user)) {
      items.add(
        _buildDrawerItem(Icons.school_outlined, 'LMS', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/lms');
        }),
      );
    }

    // 18. Study Materials
    if (_canAccess(permissionsProvider, 'lms', user)) {
      items.add(
        _buildDrawerItem(Icons.description, 'Study Materials', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/study-materials');
        }),
      );
    }

    // 19. Assignments
    if (_canAccess(permissionsProvider, 'lms', user)) {
      items.add(
        _buildDrawerItem(Icons.assignment, 'Assignments', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/assignments');
        }),
      );
    }

    // 20. Online Quizzes
    if (_canAccess(permissionsProvider, 'lms', user)) {
      items.add(
        _buildDrawerItem(Icons.quiz, 'Online Quizzes', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/quizzes');
        }),
      );
    }

    // 21. Communication
    if (_canAccess(permissionsProvider, 'communication', user)) {
      items.add(
        _buildDrawerItem(Icons.message, 'Communication', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/communication');
        }),
      );
    }

    // 22. Announcements
    if (_canAccess(permissionsProvider, 'communication', user)) {
      items.add(
        _buildDrawerItem(Icons.campaign, 'Announcements', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/announcements');
        }),
      );
    }

    // 23. Messages
    if (_canAccess(permissionsProvider, 'communication', user)) {
      items.add(
        _buildDrawerItem(Icons.chat, 'Messages', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/messages');
        }),
      );
    }

    // 24. Events & Calendar
    if (_canAccess(permissionsProvider, 'events', user)) {
      items.add(
        _buildDrawerItem(Icons.event, 'Events & Calendar', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/events');
        }),
      );
    }

    // 25. Support Ticket
    if (_canAccess(permissionsProvider, 'support', user)) {
      items.add(
        _buildDrawerItem(Icons.support_agent, 'Support Ticket', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/teacher/support');
        }),
      );
    }

    // 26. My Profile
    items.add(
      _buildDrawerItem(Icons.person, 'My Profile', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/teacher/profile');
      }),
    );

    return items;
  }

  /// Builds student menu items in the exact order for student portal
  /// All items must filter data by student ID
  List<Widget> _buildStudentMenuItems(
    BuildContext context,
    PermissionsProvider permissionsProvider,
    user,
  ) {
    final items = <Widget>[];

    // 1. My Classes
    items.add(
      _buildDrawerItem(Icons.class_, 'My Classes', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/student/classes');
      }),
    );

    // 2. My Subjects
    items.add(
      _buildDrawerItem(Icons.subject, 'My Subjects', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/student/subjects');
      }),
    );

    // 3. My Timetable
    items.add(
      _buildDrawerItem(Icons.schedule, 'My Timetable', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/student/timetable');
      }),
    );

    // 4. My Attendance
    items.add(
      _buildDrawerItem(Icons.how_to_reg, 'My Attendance', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/student/attendance');
      }),
    );

    // 5. Marks & Results
    items.add(
      _buildDrawerItem(Icons.emoji_events, 'Marks & Results', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/student/marks');
      }),
    );

    // 6. Report Cards
    items.add(
      _buildDrawerItem(Icons.description, 'Report Cards', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/student/report-cards');
      }),
    );

    // 7. My Fees
    items.add(
      _buildDrawerItem(Icons.account_balance_wallet, 'My Fees', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/student/fees');
      }),
    );

    // 8. Financial Statement
    items.add(
      _buildDrawerItem(Icons.receipt_long, 'Financial Statement', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/student/financial-statement');
      }),
    );

    // 9. Assignments
    if (_canAccess(permissionsProvider, 'lms', user)) {
      items.add(
        _buildDrawerItem(Icons.assignment, 'Assignments', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/student/assignments');
        }),
      );
    }

    // 10. Study Materials
    if (_canAccess(permissionsProvider, 'lms', user)) {
      items.add(
        _buildDrawerItem(Icons.library_books, 'Study Materials', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/student/study-materials');
        }),
      );
    }

    // 11. Online Quizzes
    if (_canAccess(permissionsProvider, 'lms', user)) {
      items.add(
        _buildDrawerItem(Icons.quiz, 'Online Quizzes', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/student/quizzes');
        }),
      );
    }

    // 12. Announcements
    if (_canAccess(permissionsProvider, 'communication', user)) {
      items.add(
        _buildDrawerItem(Icons.campaign, 'Announcements', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/student/announcements');
        }),
      );
    }

    // 12. Messages
    if (_canAccess(permissionsProvider, 'communication', user)) {
      items.add(
        _buildDrawerItem(Icons.message, 'Messages', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/student/messages');
        }),
      );
    }

    // 13. Events & Calendar
    if (_canAccess(permissionsProvider, 'events', user)) {
      items.add(
        _buildDrawerItem(Icons.event, 'Events & Calendar', () {
          Navigator.pop(context);
          Navigator.pushNamed(context, '/student/events');
        }),
      );
    }

    // 14. My Profile
    items.add(
      _buildDrawerItem(Icons.person, 'My Profile', () {
        Navigator.pop(context);
        Navigator.pushNamed(context, '/student/profile');
      }),
    );

    return items;
  }

  Widget _buildDrawerItem(IconData icon, String title, VoidCallback onTap) {
    return ListTile(
      leading: Icon(icon, color: Colors.black54),
      title: Text(title, style: const TextStyle(fontWeight: FontWeight.w500)),
      onTap: onTap,
    );
  }
}
