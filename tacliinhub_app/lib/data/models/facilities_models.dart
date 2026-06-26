/// Facilities Models for TacliinHub
library;

class Hostel {
  final int id;
  final String name;
  final String? description;
  final String? address;
  final String? contactPerson;
  final String? contactPhone;
  final String? contactEmail;
  final int totalRooms;
  final int availableRooms;
  final int totalCapacity;
  final int occupiedCapacity;
  final String status; // Active, Inactive
  final int? branchId;
  final String? branchName;
  final String createdAt;
  final String? updatedAt;

  Hostel({
    required this.id,
    required this.name,
    this.description,
    this.address,
    this.contactPerson,
    this.contactPhone,
    this.contactEmail,
    required this.totalRooms,
    required this.availableRooms,
    required this.totalCapacity,
    required this.occupiedCapacity,
    required this.status,
    this.branchId,
    this.branchName,
    required this.createdAt,
    this.updatedAt,
  });

  factory Hostel.fromJson(Map<String, dynamic> json) {
    return Hostel(
      id: _parseInt(json['id']),
      name: json['hostel_name'] ?? json['name'] ?? '',
      description: json['description'],
      address: json['address'],
      contactPerson: json['contact_person'],
      contactPhone: json['contact_phone'],
      contactEmail: json['contact_email'],
      totalRooms: _parseInt(json['total_rooms'] ?? json['rooms'] ?? 0),
      availableRooms: _parseInt(json['available_rooms'] ?? 0),
      totalCapacity: _parseInt(json['total_capacity'] ?? json['capacity'] ?? 0),
      occupiedCapacity: _parseInt(json['occupied_capacity'] ?? json['occupied'] ?? 0),
      status: json['status'] ?? 'Active',
      branchId: json['branch_id'] != null ? _parseInt(json['branch_id']) : null,
      branchName: json['branch_name'],
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'],
    );
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }
}

class HostelRoom {
  final int id;
  final int hostelId;
  final String hostelName;
  final String roomNumber;
  final String roomType; // Single, Double, Triple, Dormitory
  final int capacity;
  final int occupied;
  final int available;
  final double? rentPerMonth;
  final String? facilities; // JSON or comma-separated
  final String status; // Available, Occupied, Maintenance
  final String createdAt;
  final String? updatedAt;

  HostelRoom({
    required this.id,
    required this.hostelId,
    required this.hostelName,
    required this.roomNumber,
    required this.roomType,
    required this.capacity,
    required this.occupied,
    required this.available,
    this.rentPerMonth,
    this.facilities,
    required this.status,
    required this.createdAt,
    this.updatedAt,
  });

  factory HostelRoom.fromJson(Map<String, dynamic> json) {
    final capacity = _parseInt(json['capacity'] ?? json['room_capacity'] ?? 0);
    final occupied = _parseInt(json['occupied'] ?? json['occupied_beds'] ?? 0);
    final available = capacity - occupied;
    
    return HostelRoom(
      id: _parseInt(json['id']),
      hostelId: _parseInt(json['hostel_id']),
      hostelName: json['hostel_name'] ?? '',
      roomNumber: json['room_number'] ?? json['room_no'] ?? '',
      roomType: json['room_type'] ?? 'Single',
      capacity: capacity,
      occupied: occupied,
      available: available,
      rentPerMonth: json['rent_per_month'] != null ? _parseDouble(json['rent_per_month']) : null,
      facilities: json['facilities'],
      status: available > 0 ? 'Available' : 'Occupied',
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'],
    );
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }
}

class HostelAllocation {
  final int id;
  final int hostelId;
  final String hostelName;
  final int roomId;
  final String roomNumber;
  final int studentId;
  final String studentName;
  final String? studentIdNumber;
  final String allocationDate;
  final String? deallocationDate;
  final String status; // Active, Completed
  final double? monthlyRent;
  final String? remarks;
  final int allocatedBy;
  final String? allocatedByName;
  final String createdAt;
  final String? updatedAt;

  HostelAllocation({
    required this.id,
    required this.hostelId,
    required this.hostelName,
    required this.roomId,
    required this.roomNumber,
    required this.studentId,
    required this.studentName,
    this.studentIdNumber,
    required this.allocationDate,
    this.deallocationDate,
    required this.status,
    this.monthlyRent,
    this.remarks,
    required this.allocatedBy,
    this.allocatedByName,
    required this.createdAt,
    this.updatedAt,
  });

  factory HostelAllocation.fromJson(Map<String, dynamic> json) {
    return HostelAllocation(
      id: _parseInt(json['id']),
      hostelId: _parseInt(json['hostel_id']),
      hostelName: json['hostel_name'] ?? '',
      roomId: _parseInt(json['room_id']),
      roomNumber: json['room_number'] ?? '',
      studentId: _parseInt(json['student_id']),
      studentName: json['student_name'] ?? 
                   '${json['first_name'] ?? ''} ${json['last_name'] ?? ''}'.trim(),
      studentIdNumber: json['student_id_number'] ?? json['admission_no'],
      allocationDate: json['allocation_date'] ?? json['allocated_date'] ?? '',
      deallocationDate: json['deallocation_date'],
      status: json['status'] ?? 'Active',
      monthlyRent: json['monthly_rent'] != null ? _parseDouble(json['monthly_rent']) : null,
      remarks: json['remarks'],
      allocatedBy: _parseInt(json['allocated_by'] ?? json['allocated_by_user_id'] ?? 0),
      allocatedByName: json['allocated_by_name'],
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'],
    );
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }
}

class TransportRoute {
  final int id;
  final String routeName;
  final String? routeCode;
  final String startLocation;
  final String endLocation;
  final double? distance; // in km
  final double? fare;
  final String? description;
  final String status; // Active, Inactive
  final int? branchId;
  final String? branchName;
  final String createdAt;
  final String? updatedAt;

  TransportRoute({
    required this.id,
    required this.routeName,
    this.routeCode,
    required this.startLocation,
    required this.endLocation,
    this.distance,
    this.fare,
    this.description,
    required this.status,
    this.branchId,
    this.branchName,
    required this.createdAt,
    this.updatedAt,
  });

  factory TransportRoute.fromJson(Map<String, dynamic> json) {
    return TransportRoute(
      id: _parseInt(json['id']),
      routeName: json['route_name'] ?? json['name'] ?? '',
      routeCode: json['route_code'] ?? json['code'],
      startLocation: json['start_location'] ?? json['start_point'] ?? '',
      endLocation: json['end_location'] ?? json['end_point'] ?? '',
      distance: json['distance'] != null ? _parseDouble(json['distance']) : null,
      fare: json['fare'] != null ? _parseDouble(json['fare']) : null,
      description: json['description'],
      status: json['status'] ?? 'Active',
      branchId: json['branch_id'] != null ? _parseInt(json['branch_id']) : null,
      branchName: json['branch_name'],
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'],
    );
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }
}

class Vehicle {
  final int id;
  final String vehicleNumber;
  final String vehicleType; // Bus, Van, Car
  final String? make;
  final String? model;
  final int? year;
  final String? color;
  final int capacity;
  final String? driverName;
  final String? driverPhone;
  final String? driverLicense;
  final String status; // Active, Inactive, Maintenance
  final int? routeId;
  final String? routeName;
  final int? branchId;
  final String? branchName;
  final String createdAt;
  final String? updatedAt;

  Vehicle({
    required this.id,
    required this.vehicleNumber,
    required this.vehicleType,
    this.make,
    this.model,
    this.year,
    this.color,
    required this.capacity,
    this.driverName,
    this.driverPhone,
    this.driverLicense,
    required this.status,
    this.routeId,
    this.routeName,
    this.branchId,
    this.branchName,
    required this.createdAt,
    this.updatedAt,
  });

  factory Vehicle.fromJson(Map<String, dynamic> json) {
    return Vehicle(
      id: _parseInt(json['id']),
      vehicleNumber: json['vehicle_number'] ?? json['vehicle_no'] ?? json['registration_number'] ?? '',
      vehicleType: json['vehicle_type'] ?? 'Bus',
      make: json['make'] ?? json['manufacturer'],
      model: json['model'],
      year: json['year'] != null ? _parseInt(json['year']) : null,
      color: json['color'],
      capacity: _parseInt(json['capacity'] ?? json['seating_capacity'] ?? 0),
      driverName: json['driver_name'],
      driverPhone: json['driver_phone'],
      driverLicense: json['driver_license'],
      status: json['status'] ?? 'Active',
      routeId: json['route_id'] != null ? _parseInt(json['route_id']) : null,
      routeName: json['route_name'],
      branchId: json['branch_id'] != null ? _parseInt(json['branch_id']) : null,
      branchName: json['branch_name'],
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'],
    );
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }
}

class TransportAssignment {
  final int id;
  final int routeId;
  final String routeName;
  final int vehicleId;
  final String vehicleNumber;
  final int studentId;
  final String studentName;
  final String? studentIdNumber;
  final String assignmentDate;
  final String? endDate;
  final String status; // Active, Completed
  final double? monthlyFee;
  final String? pickupPoint;
  final String? dropPoint;
  final String? remarks;
  final int assignedBy;
  final String? assignedByName;
  final String createdAt;
  final String? updatedAt;

  TransportAssignment({
    required this.id,
    required this.routeId,
    required this.routeName,
    required this.vehicleId,
    required this.vehicleNumber,
    required this.studentId,
    required this.studentName,
    this.studentIdNumber,
    required this.assignmentDate,
    this.endDate,
    required this.status,
    this.monthlyFee,
    this.pickupPoint,
    this.dropPoint,
    this.remarks,
    required this.assignedBy,
    this.assignedByName,
    required this.createdAt,
    this.updatedAt,
  });

  factory TransportAssignment.fromJson(Map<String, dynamic> json) {
    return TransportAssignment(
      id: _parseInt(json['id']),
      routeId: _parseInt(json['route_id']),
      routeName: json['route_name'] ?? '',
      vehicleId: _parseInt(json['vehicle_id']),
      vehicleNumber: json['vehicle_number'] ?? json['vehicle_no'] ?? '',
      studentId: _parseInt(json['student_id']),
      studentName: json['student_name'] ?? 
                   '${json['first_name'] ?? ''} ${json['last_name'] ?? ''}'.trim(),
      studentIdNumber: json['student_id_number'] ?? json['admission_no'],
      assignmentDate: json['assignment_date'] ?? json['assigned_date'] ?? '',
      endDate: json['end_date'],
      status: json['status'] ?? 'Active',
      monthlyFee: json['monthly_fee'] != null ? _parseDouble(json['monthly_fee']) : null,
      pickupPoint: json['pickup_point'],
      dropPoint: json['drop_point'],
      remarks: json['remarks'],
      assignedBy: _parseInt(json['assigned_by'] ?? json['assigned_by_user_id'] ?? 0),
      assignedByName: json['assigned_by_name'],
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'],
    );
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }
}

class VehicleMaintenance {
  final int id;
  final int vehicleId;
  final String vehicleNumber;
  final String maintenanceType; // Regular, Repair, Service
  final String maintenanceDate;
  final double cost;
  final String? description;
  final String? serviceProvider;
  final int? odometerReading;
  final String? nextMaintenanceDate;
  final String status; // Completed, Pending, In Progress
  final String? remarks;
  final int recordedBy;
  final String? recordedByName;
  final String createdAt;
  final String? updatedAt;

  VehicleMaintenance({
    required this.id,
    required this.vehicleId,
    required this.vehicleNumber,
    required this.maintenanceType,
    required this.maintenanceDate,
    required this.cost,
    this.description,
    this.serviceProvider,
    this.odometerReading,
    this.nextMaintenanceDate,
    required this.status,
    this.remarks,
    required this.recordedBy,
    this.recordedByName,
    required this.createdAt,
    this.updatedAt,
  });

  factory VehicleMaintenance.fromJson(Map<String, dynamic> json) {
    return VehicleMaintenance(
      id: _parseInt(json['id']),
      vehicleId: _parseInt(json['vehicle_id']),
      vehicleNumber: json['vehicle_number'] ?? json['vehicle_no'] ?? '',
      maintenanceType: json['maintenance_type'] ?? 'Regular',
      maintenanceDate: json['maintenance_date'] ?? '',
      cost: _parseDouble(json['cost'] ?? json['amount'] ?? 0),
      description: json['description'],
      serviceProvider: json['service_provider'],
      odometerReading: json['odometer_reading'] != null ? _parseInt(json['odometer_reading']) : null,
      nextMaintenanceDate: json['next_maintenance_date'],
      status: json['status'] ?? 'Completed',
      remarks: json['remarks'],
      recordedBy: _parseInt(json['recorded_by'] ?? json['recorded_by_user_id'] ?? 0),
      recordedByName: json['recorded_by_name'],
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'],
    );
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }
}

