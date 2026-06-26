import 'package:flutter/foundation.dart';
import '../../data/repositories/facilities_repository.dart';
import '../../data/models/facilities_models.dart';

class FacilitiesProvider with ChangeNotifier {
  final FacilitiesRepository _repository = FacilitiesRepository();

  // Hostels
  List<Hostel> _hostels = [];
  List<HostelRoom> _hostelRooms = [];
  List<HostelAllocation> _hostelAllocations = [];

  // Transport
  List<TransportRoute> _transportRoutes = [];
  List<Vehicle> _vehicles = [];
  List<TransportAssignment> _transportAssignments = [];

  // Maintenance
  List<VehicleMaintenance> _vehicleMaintenance = [];

  bool _isLoading = false;
  String? _error;

  // Getters
  List<Hostel> get hostels => _hostels;
  List<HostelRoom> get hostelRooms => _hostelRooms;
  List<HostelAllocation> get hostelAllocations => _hostelAllocations;
  List<TransportRoute> get transportRoutes => _transportRoutes;
  List<Vehicle> get vehicles => _vehicles;
  List<TransportAssignment> get transportAssignments => _transportAssignments;
  List<VehicleMaintenance> get vehicleMaintenance => _vehicleMaintenance;
  bool get isLoading => _isLoading;
  String? get error => _error;

  // Helper getters
  List<HostelRoom> getAvailableRooms(int hostelId) {
    return _hostelRooms
        .where((room) => room.hostelId == hostelId && room.available > 0)
        .toList();
  }

  List<HostelAllocation> getActiveAllocations(int? hostelId) {
    return _hostelAllocations
        .where((alloc) =>
            alloc.status == 'Active' &&
            (hostelId == null || alloc.hostelId == hostelId))
        .toList();
  }

  List<Vehicle> getAvailableVehicles(int? routeId) {
    return _vehicles
        .where((vehicle) =>
            vehicle.status == 'Active' &&
            (routeId == null || vehicle.routeId == routeId))
        .toList();
  }

  List<TransportAssignment> getActiveAssignments(int? routeId) {
    return _transportAssignments
        .where((assign) =>
            assign.status == 'Active' &&
            (routeId == null || assign.routeId == routeId))
        .toList();
  }

  // ========== HOSTELS ==========
  Future<void> loadHostels({int? userId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _hostels = await _repository.getHostels(userId: userId);
      _error = null;
    } catch (e) {
      _error = e.toString();
      _hostels = [];
    } finally {
      _isLoading = false;
      notifyListeners();
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
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addHostel(
        name: name,
        description: description,
        address: address,
        contactPerson: contactPerson,
        contactPhone: contactPhone,
        contactEmail: contactEmail,
        totalRooms: totalRooms,
        totalCapacity: totalCapacity,
        userId: userId,
      );

      if (success) {
        await loadHostels(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ========== HOSTEL ROOMS ==========
  Future<void> loadHostelRooms({int? hostelId, int? userId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _hostelRooms = await _repository.getHostelRooms(
        hostelId: hostelId,
        userId: userId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString();
      _hostelRooms = [];
    } finally {
      _isLoading = false;
      notifyListeners();
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
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addHostelRoom(
        hostelId: hostelId,
        roomNumber: roomNumber,
        roomType: roomType,
        capacity: capacity,
        rentPerMonth: rentPerMonth,
        facilities: facilities,
        userId: userId,
      );

      if (success) {
        await loadHostelRooms(hostelId: hostelId, userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ========== HOSTEL ALLOCATIONS ==========
  Future<void> loadHostelAllocations({
    int? hostelId,
    int? studentId,
    String? status,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _hostelAllocations = await _repository.getHostelAllocations(
        hostelId: hostelId,
        studentId: studentId,
        status: status,
        userId: userId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString();
      _hostelAllocations = [];
    } finally {
      _isLoading = false;
      notifyListeners();
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
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.allocateHostel(
        hostelId: hostelId,
        roomId: roomId,
        studentId: studentId,
        allocationDate: allocationDate,
        monthlyRent: monthlyRent,
        remarks: remarks,
        userId: userId,
      );

      if (success) {
        await loadHostelAllocations(userId: userId);
        await loadHostelRooms(hostelId: hostelId, userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> deallocateHostel({
    required int allocationId,
    required String deallocationDate,
    String? remarks,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.deallocateHostel(
        allocationId: allocationId,
        deallocationDate: deallocationDate,
        remarks: remarks,
        userId: userId,
      );

      if (success) {
        await loadHostelAllocations(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ========== TRANSPORT ROUTES ==========
  Future<void> loadTransportRoutes({int? userId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _transportRoutes = await _repository.getTransportRoutes(userId: userId);
      _error = null;
    } catch (e) {
      _error = e.toString();
      _transportRoutes = [];
    } finally {
      _isLoading = false;
      notifyListeners();
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
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addTransportRoute(
        routeName: routeName,
        routeCode: routeCode,
        startLocation: startLocation,
        endLocation: endLocation,
        distance: distance,
        fare: fare,
        description: description,
        userId: userId,
      );

      if (success) {
        await loadTransportRoutes(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ========== VEHICLES ==========
  Future<void> loadVehicles({int? routeId, int? userId}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _vehicles = await _repository.getVehicles(
        routeId: routeId,
        userId: userId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString();
      _vehicles = [];
    } finally {
      _isLoading = false;
      notifyListeners();
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
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addVehicle(
        vehicleNumber: vehicleNumber,
        vehicleType: vehicleType,
        make: make,
        model: model,
        year: year,
        color: color,
        capacity: capacity,
        driverName: driverName,
        driverPhone: driverPhone,
        driverLicense: driverLicense,
        routeId: routeId,
        userId: userId,
      );

      if (success) {
        await loadVehicles(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ========== TRANSPORT ASSIGNMENTS ==========
  Future<void> loadTransportAssignments({
    int? routeId,
    int? vehicleId,
    int? studentId,
    String? status,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _transportAssignments = await _repository.getTransportAssignments(
        routeId: routeId,
        vehicleId: vehicleId,
        studentId: studentId,
        status: status,
        userId: userId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString();
      _transportAssignments = [];
    } finally {
      _isLoading = false;
      notifyListeners();
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
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.assignTransport(
        routeId: routeId,
        vehicleId: vehicleId,
        studentId: studentId,
        assignmentDate: assignmentDate,
        monthlyFee: monthlyFee,
        pickupPoint: pickupPoint,
        dropPoint: dropPoint,
        remarks: remarks,
        userId: userId,
      );

      if (success) {
        await loadTransportAssignments(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> unassignTransport({
    required int assignmentId,
    required String endDate,
    String? remarks,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.unassignTransport(
        assignmentId: assignmentId,
        endDate: endDate,
        remarks: remarks,
        userId: userId,
      );

      if (success) {
        await loadTransportAssignments(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ========== VEHICLE MAINTENANCE ==========
  Future<void> loadVehicleMaintenance({
    int? vehicleId,
    String? status,
    int? userId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _vehicleMaintenance = await _repository.getVehicleMaintenance(
        vehicleId: vehicleId,
        status: status,
        userId: userId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString();
      _vehicleMaintenance = [];
    } finally {
      _isLoading = false;
      notifyListeners();
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
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addVehicleMaintenance(
        vehicleId: vehicleId,
        maintenanceType: maintenanceType,
        maintenanceDate: maintenanceDate,
        cost: cost,
        description: description,
        serviceProvider: serviceProvider,
        odometerReading: odometerReading,
        nextMaintenanceDate: nextMaintenanceDate,
        remarks: remarks,
        userId: userId,
      );

      if (success) {
        await loadVehicleMaintenance(userId: userId);
      }
      return success;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}

