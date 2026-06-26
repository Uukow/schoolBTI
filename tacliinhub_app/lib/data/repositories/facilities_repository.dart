import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/constants.dart';
import '../models/facilities_models.dart';

class FacilitiesRepository {
  // Facilities endpoints are in /ajax/facilities/, not /api/ajax/facilities/
  final String baseUrl = AppConstants.baseUrl.replaceAll('/api', '');

  // ========== HOSTELS ==========
  Future<List<Hostel>> getHostels({int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/get-hostels.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> hostelsJson = data['data'] ?? [];
          return hostelsJson.map((json) => Hostel.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load hostels');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load hostels: $e');
    }
  }

  Future<bool> addHostel({
    required String name,
    String? description,
    String? address,
    String? contactPerson,
    String? contactPhone,
    String? contactEmail,
    required int totalRooms,
    required int totalCapacity,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/add-hostel.php');
      final body = {
        'name': name,
        'description': description,
        'address': address,
        'contact_person': contactPerson,
        'contact_phone': contactPhone,
        'contact_email': contactEmail,
        'total_rooms': totalRooms,
        'total_capacity': totalCapacity,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to add hostel: $e');
    }
  }

  // ========== HOSTEL ROOMS ==========
  Future<List<HostelRoom>> getHostelRooms({int? hostelId, int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/get-hostel-rooms.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (hostelId != null) queryParams['hostel_id'] = hostelId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> roomsJson = data['data'] ?? [];
          return roomsJson.map((json) => HostelRoom.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load rooms');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load rooms: $e');
    }
  }

  Future<bool> addHostelRoom({
    required int hostelId,
    required String roomNumber,
    required String roomType,
    required int capacity,
    double? rentPerMonth,
    String? facilities,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/add-hostel-room.php');
      final body = {
        'hostel_id': hostelId,
        'room_number': roomNumber,
        'room_type': roomType,
        'capacity': capacity,
        if (rentPerMonth != null) 'rent_per_month': rentPerMonth,
        if (facilities != null) 'facilities': facilities,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to add room: $e');
    }
  }

  // ========== HOSTEL ALLOCATIONS ==========
  Future<List<HostelAllocation>> getHostelAllocations({
    int? hostelId,
    int? studentId,
    String? status,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/get-hostel-allocations.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (hostelId != null) queryParams['hostel_id'] = hostelId.toString();
      if (studentId != null) queryParams['student_id'] = studentId.toString();
      if (status != null) queryParams['status'] = status;

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> allocationsJson = data['data'] ?? [];
          return allocationsJson.map((json) => HostelAllocation.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load allocations');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load allocations: $e');
    }
  }

  Future<bool> allocateHostel({
    required int hostelId,
    required int roomId,
    required int studentId,
    required String allocationDate,
    double? monthlyRent,
    String? remarks,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/allocate-hostel.php');
      final body = {
        'hostel_id': hostelId,
        'room_id': roomId,
        'student_id': studentId,
        'allocation_date': allocationDate,
        if (monthlyRent != null) 'monthly_rent': monthlyRent,
        if (remarks != null) 'remarks': remarks,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to allocate hostel: $e');
    }
  }

  Future<bool> deallocateHostel({
    required int allocationId,
    required String deallocationDate,
    String? remarks,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/deallocate-hostel.php');
      final body = {
        'allocation_id': allocationId,
        'deallocation_date': deallocationDate,
        if (remarks != null) 'remarks': remarks,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to deallocate hostel: $e');
    }
  }

  // ========== TRANSPORT ROUTES ==========
  Future<List<TransportRoute>> getTransportRoutes({int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/get-transport-routes.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> routesJson = data['data'] ?? [];
          return routesJson.map((json) => TransportRoute.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load routes');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load routes: $e');
    }
  }

  Future<bool> addTransportRoute({
    required String routeName,
    String? routeCode,
    required String startLocation,
    required String endLocation,
    double? distance,
    double? fare,
    String? description,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/add-transport-route.php');
      final body = {
        'route_name': routeName,
        if (routeCode != null) 'route_code': routeCode,
        'start_location': startLocation,
        'end_location': endLocation,
        if (distance != null) 'distance': distance,
        if (fare != null) 'fare': fare,
        if (description != null) 'description': description,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to add route: $e');
    }
  }

  // ========== VEHICLES ==========
  Future<List<Vehicle>> getVehicles({int? routeId, int? userId}) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/get-vehicles.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (routeId != null) queryParams['route_id'] = routeId.toString();

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> vehiclesJson = data['data'] ?? [];
          return vehiclesJson.map((json) => Vehicle.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load vehicles');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load vehicles: $e');
    }
  }

  Future<bool> addVehicle({
    required String vehicleNumber,
    required String vehicleType,
    String? make,
    String? model,
    int? year,
    String? color,
    required int capacity,
    String? driverName,
    String? driverPhone,
    String? driverLicense,
    int? routeId,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/add-vehicle.php');
      final body = {
        'vehicle_number': vehicleNumber,
        'vehicle_type': vehicleType,
        if (make != null) 'make': make,
        if (model != null) 'model': model,
        if (year != null) 'year': year,
        if (color != null) 'color': color,
        'capacity': capacity,
        if (driverName != null) 'driver_name': driverName,
        if (driverPhone != null) 'driver_phone': driverPhone,
        if (driverLicense != null) 'driver_license': driverLicense,
        if (routeId != null) 'route_id': routeId,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to add vehicle: $e');
    }
  }

  // ========== TRANSPORT ASSIGNMENTS ==========
  Future<List<TransportAssignment>> getTransportAssignments({
    int? routeId,
    int? vehicleId,
    int? studentId,
    String? status,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/get-transport-assignments.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (routeId != null) queryParams['route_id'] = routeId.toString();
      if (vehicleId != null) queryParams['vehicle_id'] = vehicleId.toString();
      if (studentId != null) queryParams['student_id'] = studentId.toString();
      if (status != null) queryParams['status'] = status;

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> assignmentsJson = data['data'] ?? [];
          return assignmentsJson.map((json) => TransportAssignment.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load assignments');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load assignments: $e');
    }
  }

  Future<bool> assignTransport({
    required int routeId,
    required int vehicleId,
    required int studentId,
    required String assignmentDate,
    double? monthlyFee,
    String? pickupPoint,
    String? dropPoint,
    String? remarks,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/assign-transport.php');
      final body = {
        'route_id': routeId,
        'vehicle_id': vehicleId,
        'student_id': studentId,
        'assignment_date': assignmentDate,
        if (monthlyFee != null) 'monthly_fee': monthlyFee,
        if (pickupPoint != null) 'pickup_point': pickupPoint,
        if (dropPoint != null) 'drop_point': dropPoint,
        if (remarks != null) 'remarks': remarks,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to assign transport: $e');
    }
  }

  Future<bool> unassignTransport({
    required int assignmentId,
    required String endDate,
    String? remarks,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/unassign-transport.php');
      final body = {
        'assignment_id': assignmentId,
        'end_date': endDate,
        if (remarks != null) 'remarks': remarks,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to unassign transport: $e');
    }
  }

  // ========== VEHICLE MAINTENANCE ==========
  Future<List<VehicleMaintenance>> getVehicleMaintenance({
    int? vehicleId,
    String? status,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/get-vehicle-maintenance.php');
      final queryParams = <String, String>{};
      if (userId != null) queryParams['user_id'] = userId.toString();
      if (vehicleId != null) queryParams['vehicle_id'] = vehicleId.toString();
      if (status != null) queryParams['status'] = status;

      final response = await http.get(uri.replace(queryParameters: queryParams));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          final List<dynamic> maintenanceJson = data['data'] ?? [];
          return maintenanceJson.map((json) => VehicleMaintenance.fromJson(json)).toList();
        } else {
          throw Exception(data['message'] ?? 'Failed to load maintenance records');
        }
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load maintenance records: $e');
    }
  }

  Future<bool> addVehicleMaintenance({
    required int vehicleId,
    required String maintenanceType,
    required String maintenanceDate,
    required double cost,
    String? description,
    String? serviceProvider,
    int? odometerReading,
    String? nextMaintenanceDate,
    String? remarks,
    int? userId,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/ajax/facilities/add-vehicle-maintenance.php');
      final body = {
        'vehicle_id': vehicleId,
        'maintenance_type': maintenanceType,
        'maintenance_date': maintenanceDate,
        'cost': cost,
        if (description != null) 'description': description,
        if (serviceProvider != null) 'service_provider': serviceProvider,
        if (odometerReading != null) 'odometer_reading': odometerReading,
        if (nextMaintenanceDate != null) 'next_maintenance_date': nextMaintenanceDate,
        if (remarks != null) 'remarks': remarks,
        if (userId != null) 'user_id': userId,
      };

      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] == true;
      } else {
        throw Exception('Server error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to add maintenance record: $e');
    }
  }
}

