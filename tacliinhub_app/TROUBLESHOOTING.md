## 🔧 TROUBLESHOOTING: "Oops! Something went wrong"

The API is working correctly on the server! The issue is the app can't connect from the emulator.

### Quick Solutions:

#### Option 1: Hot Restart the App (RECOMMENDED)
1. Press `R` in the terminal where Flutter is running
2. Or click the hot restart button in your IDE
3. The cache will clear automatically on the next load

#### Option 2: Uninstall and Reinstall
```bash
# In your terminal
flutter clean
flutter pub get
flutter run
```

#### Option 3: Check Network Configuration

**For Android Emulator:**
- Make sure XAMPP is running on Windows
- The URL `http://10.0.2.2/bti/api` should work
- Try accessing http://10.0.2.2/bti in the emulator's Chrome browser

**For Real Device:**
1. Find your PC's IP address:
   ```bash
   ipconfig
   # Look for IPv4 Address under your WiFi/Ethernet adapter
   # Example: 192.168.1.5
   ```

2. Update `lib/core/constants.dart`:
   ```dart
   static const String baseUrl = 'http://YOUR_IP/bti/api';
   // Example: 'http://192.168.1.5/bti/api'
   ```

3. Make sure your phone and PC are on the SAME WiFi network

4. Restart the app

### Debug Steps:

1. **Check Flutter Console** - You should now see these logs:
   ```
   📡 Fetching dashboard data for user X...
   🌐 URL: http://10.0.2.2/bti/api/dashboard/index.php?user_id=X
   📨 Response status: 200
   ✅ Successfully fetched dashboard data
   ```

2. **If you see "Connection refused":**
   - XAMPP is not running
   - Wrong IP address
   - Firewall blocking connection

3. **If you see "Failed to parse":**
   - Old cached data (will be cleared on next run)
   - API returning unexpected format

### Test Your Setup:

Run this in your terminal:
```bash
# Test from your PC
Invoke-WebRequest -Uri "http://localhost/bti/api/dashboard/index.php?user_id=1" -Method GET
```

If this works (like it just did!), then the issue is just the emulator connection.

### Expected Result After Fix:

You should see your dashboard with:
- ✅ 40 Total Students (35 active)
- ✅ 11 Staff members
- ✅ 2 Classes
- ✅ 94.2% Monthly Attendance
- ✅ $193 in fees collected
- ✅ And more statistics!

### Still Not Working?

Share the Flutter console output (the terminal where you ran `flutter run`) - it will now show helpful debug messages!














