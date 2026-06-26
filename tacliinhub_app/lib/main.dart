import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'core/theme.dart';
import 'presentation/providers/auth_provider.dart';
import 'presentation/providers/dashboard_provider.dart';
import 'presentation/providers/classes_provider.dart';
import 'presentation/providers/marks_provider.dart';
import 'presentation/providers/assignments_provider.dart';
import 'presentation/providers/notification_provider.dart';
import 'presentation/providers/fee_provider.dart';
import 'presentation/providers/branch_provider.dart';
import 'presentation/providers/branch_filter_provider.dart';
import 'presentation/providers/student_provider.dart';
import 'presentation/providers/class_provider.dart';
import 'presentation/providers/admission_provider.dart';
import 'presentation/providers/academic_provider.dart';
import 'presentation/providers/attendance_provider.dart';
import 'presentation/providers/examination_provider.dart';
import 'presentation/providers/fees_provider.dart';
import 'presentation/providers/library_provider.dart';
import 'presentation/providers/facilities_provider.dart';
import 'presentation/providers/hr_provider.dart';
import 'presentation/providers/lms_provider.dart';
import 'presentation/providers/communication_provider.dart';
import 'presentation/providers/events_provider.dart';
import 'presentation/providers/reports_provider.dart';
import 'presentation/providers/settings_provider.dart';
import 'presentation/providers/support_provider.dart';
import 'presentation/providers/teacher_provider.dart';
import 'presentation/providers/permissions_provider.dart';
import 'presentation/providers/student_portal_provider.dart';
import 'data/repositories/settings_repository.dart';
import 'data/repositories/support_repository.dart';
import 'presentation/pages/splash_screen_page.dart';
import 'presentation/pages/academics_page.dart';
import 'presentation/pages/login_page.dart';
import 'presentation/pages/forgot_password_page.dart';
import 'presentation/pages/reset_password_page.dart';
import 'presentation/pages/dashboard_page.dart';
import 'presentation/pages/classes_page.dart';
import 'presentation/pages/marks_page.dart';
import 'presentation/pages/assignments_page.dart' as old_assignments;
import 'presentation/pages/notifications_page.dart';
import 'presentation/pages/timetable_page.dart';
import 'presentation/pages/profile_page.dart';
import 'presentation/pages/settings/settings_main_page.dart';
import 'presentation/pages/settings/general_settings_page.dart';
import 'presentation/pages/settings/academic_settings_page.dart';
import 'presentation/pages/settings/user_management_page.dart';
import 'presentation/pages/settings/roles_permissions_page.dart';
import 'presentation/pages/settings/granular_permissions_page.dart';
import 'presentation/pages/settings/backup_restore_page.dart';
import 'presentation/pages/settings/about_license_page.dart';
import 'presentation/pages/support/support_dashboard_page.dart';
import 'presentation/pages/support/tickets_list_page.dart';
import 'presentation/pages/support/create_ticket_page.dart';
import 'presentation/pages/support/ticket_detail_page.dart';
import 'presentation/pages/teacher/teacher_dashboard_page.dart';
import 'presentation/pages/teacher/my_classes_page.dart';
import 'presentation/pages/teacher/my_students_page.dart';
import 'presentation/pages/teacher/my_timetable_page.dart';
import 'presentation/pages/teacher/teacher_attendance_page.dart';
import 'presentation/pages/teacher/teacher_marks_page.dart';
import 'presentation/pages/teacher/teacher_lesson_plans_page.dart';
import 'presentation/pages/teacher/teacher_student_reports_page.dart';
import 'presentation/pages/teacher/teacher_reports_page.dart';
import 'presentation/pages/teacher/teacher_results_page.dart';
import 'presentation/pages/teacher/teacher_report_cards_page.dart';
import 'presentation/pages/teacher/teacher_analytics_page.dart';
import 'presentation/pages/teacher/teacher_books_page.dart';
import 'presentation/pages/teacher/teacher_library_resources_page.dart';
import 'presentation/pages/teacher/teacher_issue_history_page.dart';
import 'presentation/pages/teacher/teacher_leave_management_page.dart';
import 'presentation/pages/teacher/teacher_lms_page.dart';
import 'presentation/pages/teacher/teacher_study_materials_page.dart';
import 'presentation/pages/teacher/teacher_assignments_page.dart';
import 'presentation/pages/teacher/teacher_quizzes_page.dart';
import 'presentation/pages/teacher/teacher_communication_page.dart';
import 'presentation/pages/teacher/teacher_announcements_page.dart';
import 'presentation/pages/teacher/teacher_messages_page.dart';
import 'presentation/pages/teacher/teacher_events_page.dart';
import 'presentation/pages/teacher/teacher_support_page.dart';
import 'presentation/pages/teacher/teacher_profile_page.dart';
import 'presentation/pages/student/student_dashboard_page.dart';
import 'presentation/pages/student/student_classes_page.dart';
import 'presentation/pages/student/student_subjects_page.dart';
import 'presentation/pages/student/student_timetable_page.dart';
import 'presentation/pages/student/student_attendance_page.dart'
    as student_portal;
import 'presentation/pages/student/student_marks_page.dart';
import 'presentation/pages/student/student_report_cards_page.dart';
import 'presentation/pages/student/student_fees_page.dart';
import 'presentation/pages/student/student_financial_statement_page.dart';
import 'presentation/pages/student/student_assignments_page.dart';
import 'presentation/pages/student/student_study_materials_page.dart';
import 'presentation/pages/student/student_quizzes_page.dart';
import 'presentation/pages/student/student_announcements_page.dart';
import 'presentation/pages/student/student_messages_page.dart';
import 'presentation/pages/student/student_events_page.dart';
import 'presentation/pages/student/student_profile_page.dart';
import 'presentation/pages/branches_page.dart';
import 'presentation/pages/students_page.dart';
import 'presentation/pages/all_students_page.dart';
import 'presentation/pages/add_student_page.dart';
import 'presentation/pages/assign_sections_page.dart';
import 'presentation/pages/promote_students_page.dart';
import 'presentation/pages/student_reports_page.dart';
import 'presentation/pages/admissions_page.dart';
import 'presentation/pages/applications_page.dart';
import 'presentation/pages/pending_review_page.dart';
import 'presentation/pages/approved_page.dart';
import 'presentation/pages/add_admission_page.dart';
import 'presentation/pages/admission_statistics_page.dart';
import 'presentation/pages/attendance/attendance_dashboard_page.dart';
import 'presentation/pages/attendance/students_list_page.dart';
import 'presentation/pages/attendance/student_attendance_page.dart';
import 'presentation/pages/attendance/staff_attendance_page.dart' as attendance;
import 'presentation/pages/attendance/attendance_reports_page.dart';
import 'presentation/pages/examinations/examinations_page.dart';
import 'presentation/pages/examinations/manage_exams_page.dart';
import 'presentation/pages/examinations/exam_schedule_page.dart';
import 'presentation/pages/examinations/enter_marks_page.dart';
import 'presentation/pages/examinations/results_page.dart';
import 'presentation/pages/examinations/report_cards_page.dart';
import 'presentation/pages/examinations/analytics_page.dart';
import 'presentation/pages/fees/fees_page.dart' as fees_finance;
import 'presentation/pages/fees/fee_structure_page.dart';
import 'presentation/pages/fees/monthly_assignment_page.dart';
import 'presentation/pages/fees/flexible_payment_page.dart';
import 'presentation/pages/fees/invoices_page.dart';
import 'presentation/pages/fees/payments_page.dart';
import 'presentation/pages/fees/ledger_page.dart';
import 'presentation/pages/fees/defaulters_page.dart';
import 'presentation/pages/fees/income_page.dart';
import 'presentation/pages/fees/expenses_page.dart';
import 'presentation/pages/fees/reports_page.dart';
import 'presentation/pages/library/library_page.dart';
import 'presentation/pages/library/books_page.dart';
import 'presentation/pages/library/add_book_page.dart';
import 'presentation/pages/library/issue_book_page.dart';
import 'presentation/pages/library/return_book_page.dart';
import 'presentation/pages/library/issue_history_page.dart';
import 'presentation/pages/facilities/facilities_page.dart';
import 'presentation/pages/facilities/hostels_page.dart';
import 'presentation/pages/facilities/add_hostel_page.dart';
import 'presentation/pages/facilities/hostel_rooms_page.dart';
import 'presentation/pages/facilities/add_hostel_room_page.dart';
import 'presentation/pages/facilities/hostel_allocations_page.dart';
import 'presentation/pages/facilities/allocate_hostel_page.dart';
import 'presentation/pages/facilities/transport_routes_page.dart';
import 'presentation/pages/facilities/add_transport_route_page.dart';
import 'presentation/pages/facilities/vehicles_page.dart';
import 'presentation/pages/facilities/add_vehicle_page.dart';
import 'presentation/pages/facilities/transport_assignments_page.dart';
import 'presentation/pages/facilities/assign_transport_page.dart';
import 'presentation/pages/facilities/vehicle_maintenance_page.dart';
import 'presentation/pages/facilities/add_vehicle_maintenance_page.dart';
import 'presentation/pages/hr/hr_page.dart';
import 'presentation/pages/hr/staff_page.dart';
import 'presentation/pages/hr/add_staff_page.dart';
import 'presentation/pages/hr/payroll_page.dart';
import 'presentation/pages/hr/add_payroll_structure_page.dart';
import 'presentation/pages/hr/process_salary_page.dart';
import 'presentation/pages/hr/leave_management_page.dart';
import 'presentation/pages/hr/apply_leave_page.dart';
import 'presentation/pages/hr/staff_attendance_page.dart';
import 'presentation/pages/lms/lms_page.dart';
import 'presentation/pages/lms/study_materials_page.dart';
import 'presentation/pages/lms/add_study_material_page.dart';
import 'presentation/pages/lms/assignments_page.dart' as lms_assignments;
import 'presentation/pages/lms/add_assignment_page.dart';
import 'presentation/pages/lms/quizzes_page.dart';
import 'presentation/pages/lms/add_quiz_page.dart';
import 'presentation/pages/communication/communication_page.dart';
import 'presentation/pages/communication/announcements_page.dart';
import 'presentation/pages/communication/add_announcement_page.dart';
import 'presentation/pages/communication/messages_page.dart';
import 'presentation/pages/communication/send_message_page.dart';
import 'presentation/pages/communication/send_sms_page.dart';
import 'presentation/pages/communication/send_email_page.dart';
import 'presentation/pages/events/events_page.dart';
import 'presentation/pages/events/calendar_view_page.dart';
import 'presentation/pages/events/events_list_page.dart';
import 'presentation/pages/events/add_event_page.dart';
import 'presentation/pages/reports/reports_page.dart' as reports_module;
import 'presentation/pages/reports/student_reports_page.dart'
    as reports_student;
import 'presentation/pages/reports/academic_reports_page.dart'
    as reports_academic;
import 'presentation/pages/reports/financial_reports_page.dart'
    as reports_financial;
import 'presentation/pages/reports/attendance_reports_page.dart'
    as reports_attendance;
import 'presentation/pages/reports/custom_reports_page.dart' as reports_custom;

void main() {
  runApp(const TacliinHubApp());
}

class TacliinHubApp extends StatelessWidget {
  const TacliinHubApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(
          create: (context) {
            final branchFilterProvider = BranchFilterProvider();
            // Initialize after auth is ready
            WidgetsBinding.instance.addPostFrameCallback((_) {
              final authProvider = Provider.of<AuthProvider>(
                context,
                listen: false,
              );
              branchFilterProvider.initialize(authProvider);
            });
            return branchFilterProvider;
          },
        ),
        ChangeNotifierProvider(create: (_) => DashboardProvider()),
        ChangeNotifierProvider(create: (_) => ClassesProvider()),
        ChangeNotifierProvider(create: (_) => MarksProvider()),
        ChangeNotifierProvider(create: (_) => AssignmentsProvider()),
        ChangeNotifierProvider(create: (_) => NotificationProvider()),
        ChangeNotifierProvider(create: (_) => FeeProvider()),
        ChangeNotifierProvider(create: (_) => BranchProvider()),
        ChangeNotifierProvider(create: (_) => StudentProvider()),
        ChangeNotifierProvider(create: (_) => ClassProvider()),
        ChangeNotifierProvider(create: (_) => AdmissionProvider()),
        ChangeNotifierProvider(create: (_) => AcademicProvider()),
        ChangeNotifierProvider(create: (_) => AttendanceProvider()),
        ChangeNotifierProvider(create: (_) => ExaminationProvider()),
        ChangeNotifierProvider(create: (_) => FeesProvider()),
        ChangeNotifierProvider(create: (_) => LibraryProvider()),
        ChangeNotifierProvider(create: (_) => FacilitiesProvider()),
        ChangeNotifierProvider(create: (_) => HrProvider()),
        ChangeNotifierProvider(create: (_) => LmsProvider()),
        ChangeNotifierProvider(create: (_) => CommunicationProvider()),
        ChangeNotifierProvider(create: (_) => EventsProvider()),
        ChangeNotifierProvider(create: (_) => ReportsProvider()),
        ChangeNotifierProvider(create: (_) => TeacherProvider()),
        ChangeNotifierProvider(create: (_) => PermissionsProvider()),
        ChangeNotifierProvider(create: (_) => StudentPortalProvider()),
        ChangeNotifierProvider(
          create: (_) => SettingsProvider(SettingsRepository()),
        ),
        ChangeNotifierProvider(
          create: (_) => SupportProvider(SupportRepository()),
        ),
      ],
      child: MaterialApp(
        title: 'TacliinHub',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.lightTheme,
        home: const SplashScreenPage(),
        routes: {
          '/login': (context) => const LoginPage(),
          '/forgot-password': (context) => const ForgotPasswordPage(),
          '/reset-password': (context) {
            final args = ModalRoute.of(context)!.settings.arguments as Map;
            return ResetPasswordPage(token: args['token']);
          },
          '/dashboard': (context) => const DashboardPage(),
          '/classes': (context) => const ClassesPage(),
          '/marks': (context) => const MarksPage(),
          '/assignments': (context) => const old_assignments.AssignmentsPage(),
          '/notifications': (context) => const NotificationsPage(),
          '/attendance': (context) => const AttendanceDashboardPage(),
          '/timetable': (context) => const TimetablePage(),
          '/profile': (context) => const ProfilePage(),
          '/branches': (context) => const BranchesPage(),
          '/students': (context) => const StudentsPage(),
          '/all-students': (context) => const AllStudentsPage(),
          '/add-student': (context) => const AddStudentPage(),
          '/assign-sections': (context) => const AssignSectionsPage(),
          '/promote-students': (context) => const PromoteStudentsPage(),
          '/student-reports': (context) => const StudentReportsPage(),
          '/admissions': (context) => const AdmissionsPage(),
          '/admissions/applications': (context) => const ApplicationsPage(),
          '/admissions/pending': (context) => const PendingReviewPage(),
          '/admissions/approved': (context) => const ApprovedPage(),
          '/admissions/add': (context) => const AddAdmissionPage(),
          '/admissions/statistics': (context) =>
              const AdmissionStatisticsPage(),
          '/academics': (context) => const AcademicsPage(),
          '/attendance/dashboard': (context) => const AttendanceDashboardPage(),
          '/attendance/students-list': (context) => const StudentsListPage(),
          '/attendance/student': (context) => const StudentAttendancePage(),
          '/attendance/staff': (context) =>
              const attendance.StaffAttendancePage(),
          '/attendance/reports': (context) => const AttendanceReportsPage(),
          '/examinations': (context) => const ExaminationsPage(),
          '/examinations/manage-exams': (context) => const ManageExamsPage(),
          '/examinations/exam-schedule': (context) => const ExamSchedulePage(),
          '/examinations/enter-marks': (context) => const EnterMarksPage(),
          '/examinations/results': (context) => const ResultsPage(),
          '/examinations/report-cards': (context) => const ReportCardsPage(),
          '/examinations/analytics': (context) => const AnalyticsPage(),
          '/fees': (context) => const fees_finance.FeesFinancePage(),
          '/fees/fee-structure': (context) => const FeeStructurePage(),
          '/fees/monthly-assignment': (context) =>
              const MonthlyAssignmentPage(),
          '/fees/flexible-payment': (context) => const FlexiblePaymentPage(),
          '/fees/ledger': (context) => const LedgerPage(),
          '/fees/invoices': (context) => const InvoicesPage(),
          '/fees/payments': (context) => const PaymentsPage(),
          '/fees/defaulters': (context) => const DefaultersPage(),
          '/fees/income': (context) => const IncomePage(),
          '/fees/expenses': (context) => const ExpensesPage(),
          '/fees/reports': (context) => const ReportsPage(),
          '/library': (context) => const LibraryPage(),
          '/library/books': (context) => const BooksPage(),
          '/library/books/add': (context) => const AddBookPage(),
          '/library/issue': (context) => const IssueBookPage(),
          '/library/return': (context) => const ReturnBookPage(),
          '/library/history': (context) => const IssueHistoryPage(),
          '/facilities': (context) => const FacilitiesPage(),
          '/facilities/hostels': (context) => const HostelsPage(),
          '/facilities/hostels/add': (context) => const AddHostelPage(),
          '/facilities/hostel-rooms': (context) => const HostelRoomsPage(),
          '/facilities/hostel-rooms/add': (context) =>
              const AddHostelRoomPage(),
          '/facilities/hostel-allocations': (context) =>
              const HostelAllocationsPage(),
          '/facilities/hostel-allocations/allocate': (context) =>
              const AllocateHostelPage(),
          '/facilities/transport-routes': (context) =>
              const TransportRoutesPage(),
          '/facilities/transport-routes/add': (context) =>
              const AddTransportRoutePage(),
          '/facilities/vehicles': (context) => const VehiclesPage(),
          '/facilities/vehicles/add': (context) => const AddVehiclePage(),
          '/facilities/transport-assignments': (context) =>
              const TransportAssignmentsPage(),
          '/facilities/transport-assignments/assign': (context) =>
              const AssignTransportPage(),
          '/facilities/vehicle-maintenance': (context) =>
              const VehicleMaintenancePage(),
          '/facilities/vehicle-maintenance/add': (context) =>
              const AddVehicleMaintenancePage(),
          '/hr': (context) => const HrPage(),
          '/hr/staff': (context) => const StaffPage(),
          '/hr/staff/add': (context) => const AddStaffPage(),
          '/hr/payroll': (context) => const PayrollPage(),
          '/hr/payroll/add-structure': (context) =>
              const AddPayrollStructurePage(),
          '/hr/payroll/process-salary': (context) => const ProcessSalaryPage(),
          '/hr/leave': (context) => const LeaveManagementPage(),
          '/hr/leave/apply': (context) => const ApplyLeavePage(),
          '/hr/staff-attendance': (context) => const StaffAttendancePage(),
          '/lms': (context) => const LmsPage(),
          '/lms/study-materials': (context) => const StudyMaterialsPage(),
          '/lms/study-materials/add': (context) => const AddStudyMaterialPage(),
          '/lms/assignments': (context) =>
              const lms_assignments.AssignmentsPage(),
          '/lms/assignments/add': (context) => const AddAssignmentPage(),
          '/lms/quizzes': (context) => const QuizzesPage(),
          '/lms/quizzes/add': (context) => const AddQuizPage(),
          '/communication': (context) => const CommunicationPage(),
          '/communication/announcements': (context) =>
              const AnnouncementsPage(),
          '/communication/announcements/add': (context) =>
              const AddAnnouncementPage(),
          '/communication/messages': (context) => const MessagesPage(),
          '/communication/messages/send': (context) => const SendMessagePage(),
          '/communication/send-sms': (context) => const SendSmsPage(),
          '/communication/send-email': (context) => const SendEmailPage(),
          '/events': (context) => const EventsPage(),
          '/events/calendar': (context) => const CalendarViewPage(),
          '/events/list': (context) => const EventsListPage(),
          '/events/add': (context) => const AddEventPage(),
          '/reports': (context) => const reports_module.ReportsPage(),
          '/reports/student': (context) =>
              const reports_student.StudentReportsPage(),
          '/reports/academic': (context) =>
              const reports_academic.AcademicReportsPage(),
          '/reports/financial': (context) =>
              const reports_financial.FinancialReportsPage(),
          '/reports/attendance': (context) =>
              const reports_attendance.AttendanceReportsPage(),
          '/reports/custom': (context) =>
              const reports_custom.CustomReportsPage(),
          '/settings': (context) => const SettingsMainPage(),
          '/settings/general': (context) => const GeneralSettingsPage(),
          '/settings/academic': (context) => const AcademicSettingsPage(),
          '/settings/users': (context) => const UserManagementPage(),
          '/settings/roles': (context) => const RolesPermissionsPage(),
          '/settings/permissions': (context) => const GranularPermissionsPage(),
          '/settings/backup': (context) => const BackupRestorePage(),
          '/settings/about': (context) => const AboutLicensePage(),
          '/support': (context) => const SupportDashboardPage(),
          '/support/tickets': (context) => const TicketsListPage(),
          '/support/create': (context) => const CreateTicketPage(),
          '/support/ticket': (context) {
            final args = ModalRoute.of(context)!.settings.arguments as Map;
            return TicketDetailPage(ticketId: args['ticketId']);
          },
          // Teacher Portal Routes - All data filtered by teacher assignment
          '/teacher/dashboard': (context) => const TeacherDashboardPage(),
          '/teacher/classes': (context) => const MyClassesPage(),
          '/teacher/students': (context) {
            final args =
                ModalRoute.of(context)?.settings.arguments
                    as Map<String, dynamic>?;
            return MyStudentsPage(
              classId: args?['classId'],
              subjectId: args?['subjectId'],
            );
          },
          '/teacher/student-reports': (context) =>
              const TeacherStudentReportsPage(),
          '/teacher/timetable': (context) => const MyTimetablePage(),
          '/teacher/attendance': (context) => const TeacherAttendancePage(),
          '/teacher/reports': (context) => const TeacherReportsPage(),
          '/teacher/marks': (context) => const TeacherMarksPage(),
          '/teacher/results': (context) => const TeacherResultsPage(),
          '/teacher/report-cards': (context) => const TeacherReportCardsPage(),
          '/teacher/analytics': (context) => const TeacherAnalyticsPage(),
          '/teacher/lesson-plans': (context) => const TeacherLessonPlansPage(),
          '/teacher/books': (context) => const TeacherBooksPage(),
          '/teacher/library-resources': (context) =>
              const TeacherLibraryResourcesPage(),
          '/teacher/issue-history': (context) =>
              const TeacherIssueHistoryPage(),
          '/teacher/leave-management': (context) =>
              const TeacherLeaveManagementPage(),
          '/teacher/lms': (context) => const TeacherLmsPage(),
          '/teacher/study-materials': (context) =>
              const TeacherStudyMaterialsPage(),
          '/teacher/assignments': (context) => const TeacherAssignmentsPage(),
          '/teacher/quizzes': (context) => const TeacherQuizzesPage(),
          '/teacher/communication': (context) =>
              const TeacherCommunicationPage(),
          '/teacher/announcements': (context) =>
              const TeacherAnnouncementsPage(),
          '/teacher/messages': (context) => const TeacherMessagesPage(),
          '/teacher/events': (context) => const TeacherEventsPage(),
          '/teacher/support': (context) => const TeacherSupportPage(),
          '/teacher/profile': (context) => const TeacherProfilePage(),
          // Student Portal Routes - All data filtered by student ID
          '/student/dashboard': (context) => const StudentDashboardPage(),
          '/student/classes': (context) => const StudentClassesPage(),
          '/student/subjects': (context) => const StudentSubjectsPage(),
          '/student/timetable': (context) => const StudentTimetablePage(),
          '/student/attendance': (context) =>
              const student_portal.StudentAttendancePage(),
          '/student/marks': (context) => const StudentMarksPage(),
          '/student/report-cards': (context) => const StudentReportCardsPage(),
          '/student/fees': (context) => const StudentFeesPage(),
          '/student/financial-statement': (context) =>
              const StudentFinancialStatementPage(),
          '/student/assignments': (context) => const StudentAssignmentsPage(),
          '/student/study-materials': (context) =>
              const StudentStudyMaterialsPage(),
          '/student/quizzes': (context) => const StudentQuizzesPage(),
          '/student/announcements': (context) =>
              const StudentAnnouncementsPage(),
          '/student/messages': (context) => const StudentMessagesPage(),
          '/student/events': (context) => const StudentEventsPage(),
          '/student/profile': (context) => const StudentProfilePage(),
        },
      ),
    );
  }
}
