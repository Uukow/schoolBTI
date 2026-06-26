# TacliinHub Mobile App - Quick Start Guide

## 🚀 Quick Setup (5 Minutes)

### Step 1: Configure API URL
Open `lib/core/constants.dart` and update the base URL:

```dart
// For Android Emulator (default - no change needed)
static const String baseUrl = 'http://10.0.2.2/bti/api';

// For Real Device (replace X with your IP)
static const String baseUrl = 'http://192.168.1.X/bti/api';
```

**Find your IP:**
- Windows: `ipconfig` → Look for IPv4 Address
- Mac/Linux: `ifconfig` → Look for inet address

### Step 2: Install Dependencies
```bash
cd tacliinhub_app
flutter pub get
```

### Step 3: Run the App
```bash
flutter run
```

## 📱 Testing the App

### Login Credentials
Use any existing user from your TacliinHub database:
- **Student Account**: Any student credentials
- **Admin Account**: Any admin credentials
- **Teacher Account**: Any teacher credentials

### Features to Test
1. ✅ Login → View personalized dashboard
2. ✅ Pull down to refresh data
3. ✅ Click notification bell → See notifications
4. ✅ Navigate to Fees → View fee details
5. ✅ Open drawer → Access all modules
6. ✅ Test offline mode (disable WiFi/data)

## 🎨 What You'll See

### Student Dashboard
- **Header**: Personalized greeting with user name
- **Quick Overview**: 
  - Attendance percentage (color-coded)
  - Today's status (Present/Absent/Late)
  - Upcoming assignments count
  - Upcoming exams count
- **Outstanding Fees**: If any dues exist
- **Attendance Summary**: Detailed breakdown
- **Today's Schedule**: Complete timetable
- **Announcements**: Recent updates

### Admin Dashboard
- **Key Metrics**: Students, Staff, Classes
- **Financial Overview**: 
  - Revenue this month
  - Outstanding fees
  - Fee collection progress
- **Attendance Today**: Real-time stats
- **Academic Progress**: Exams and assignments
- **Alerts**: Pending tasks and issues
- **Top Classes**: Performance ranking

### Notifications Page
- List of all notifications
- Unread badge counter
- Time-ago display ("5 min ago")
- Mark as read functionality
- Pull to refresh

### Fees Page
- **Summary Tab**:
  - Total fees, paid, due, discount
  - Visual progress indicator
  - Recent invoices list
- **History Tab**:
  - Complete payment history
  - Transaction details

## 🛠️ Troubleshooting

### "Connection Refused" Error
```bash
✅ Check: Is XAMPP running?
✅ Check: Is the IP address correct in constants.dart?
✅ Test: Open http://YOUR_IP/bti in browser
```

### "No Data Available"
```bash
✅ Check: Database has student/admin/staff records
✅ Check: PHP errors in C:\xampp\htdocs\schoolerp\logs
✅ Test: Visit API directly: http://YOUR_IP/bti/api/dashboard/index.php?user_id=1
```

### Build Errors
```bash
flutter clean
flutter pub get
flutter run
```

## 📦 Build Release APK

```bash
# Build release APK
flutter build apk --release

# APK Location
build/app/outputs/flutter-apk/app-release.apk

# Install on device
adb install build/app/outputs/flutter-apk/app-release.apk
```

## 🎯 Key API Endpoints

All endpoints require `?user_id={id}` parameter:

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/auth/login.php` | POST | User login |
| `/api/dashboard/index.php` | GET | Dashboard data |
| `/api/notifications/index.php` | GET | Notifications |
| `/api/student/fees.php` | GET | Fee details |
| `/api/student/classes.php` | GET | Class list |
| `/api/student/marks.php` | GET | Marks/results |
| `/api/student/assignments.php` | GET | Assignments |

## 📁 Important Files

| File | Purpose |
|------|---------|
| `lib/core/constants.dart` | API URL & app config |
| `lib/main.dart` | App entry point |
| `lib/presentation/pages/dashboard_page.dart` | Main dashboard |
| `INTEGRATION_GUIDE.md` | Detailed documentation |
| `PROJECT_SUMMARY.md` | Feature summary |

## ✅ Success Indicators

You'll know everything works when you see:
- ✅ Smooth login flow
- ✅ Dashboard loads with real data
- ✅ Notification badge shows count
- ✅ Fees page displays invoices
- ✅ Pull-to-refresh updates data
- ✅ All cards show actual numbers from database

## 🎉 You're Ready!

The app is fully integrated and production-ready. All features are working with real-time data from your TacliinHub system.

### Next Steps (Optional)
1. Customize app icon and splash screen
2. Add Firebase for push notifications
3. Configure for iOS deployment
4. Submit to app stores

---

**Need Help?**
- Check `INTEGRATION_GUIDE.md` for detailed docs
- Review `PROJECT_SUMMARY.md` for feature list
- Test API endpoints in browser/Postman
- Check XAMPP error logs

**Happy Coding! 🚀**














