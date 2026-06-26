import 'package:flutter/material.dart';
import '../../data/repositories/fees_repository.dart';
import '../../data/models/fees_models.dart';

class FeesProvider with ChangeNotifier {
  final FeesRepository _repository = FeesRepository();

  List<FeeType> _feeTypes = [];
  List<FeeStructure> _feeStructures = [];
  List<MonthlyFeeAssignment> _monthlyAssignments = [];
  List<FeeInvoice> _invoices = [];
  List<FeePayment> _payments = [];
  List<StudentFeeLedger> _ledger = [];
  List<MonthlyFeeAssignment> _defaulters = [];
  List<Income> _income = [];
  List<Expense> _expenses = [];
  FinanceReport? _financeReport;
  bool _isLoading = false;
  String? _error;

  List<FeeType> get feeTypes => _feeTypes;
  List<FeeStructure> get feeStructures => _feeStructures;
  List<MonthlyFeeAssignment> get monthlyAssignments => _monthlyAssignments;
  List<FeeInvoice> get invoices => _invoices;
  List<FeePayment> get payments => _payments;
  List<StudentFeeLedger> get ledger => _ledger;
  List<MonthlyFeeAssignment> get defaulters => _defaulters;
  List<Income> get income => _income;
  List<Expense> get expenses => _expenses;
  FinanceReport? get financeReport => _financeReport;
  bool get isLoading => _isLoading;
  String? get error => _error;

  /// Load fee types
  Future<void> loadFeeTypes() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _feeTypes = await _repository.getFeeTypes();
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _feeTypes = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load fee structures
  Future<void> loadFeeStructures({
    int? classId,
    int? sessionId,
    BuildContext? context, // Add context for branch filtering
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _feeStructures = await _repository.getFeeStructures(
        classId: classId,
        sessionId: sessionId,
        context: context,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _feeStructures = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Create fee structure
  Future<bool> createFeeStructure({
    required int classId,
    required int feeTypeId,
    required int sessionId,
    required double amount,
    required String frequency,
    DateTime? dueDate,
    required bool isMandatory,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.createFeeStructure(
        classId: classId,
        feeTypeId: feeTypeId,
        sessionId: sessionId,
        amount: amount,
        frequency: frequency,
        dueDate: dueDate,
        isMandatory: isMandatory,
      );
      if (success) {
        await loadFeeStructures();
      }
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load monthly assignments
  Future<void> loadMonthlyAssignments({
    int? studentId,
    int? classId,
    int? feeTypeId,
    String? month,
    int? sessionId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _monthlyAssignments = await _repository.getMonthlyFeeAssignments(
        studentId: studentId,
        classId: classId,
        feeTypeId: feeTypeId,
        month: month,
        sessionId: sessionId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _monthlyAssignments = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Assign monthly fees
  Future<bool> assignMonthlyFees({
    required String month,
    required int feeTypeId,
    int? classId,
    required int sessionId,
    DateTime? dueDate,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.assignMonthlyFees(
        month: month,
        feeTypeId: feeTypeId,
        classId: classId,
        sessionId: sessionId,
        dueDate: dueDate,
      );
      if (success) {
        await loadMonthlyAssignments();
      }
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Record flexible payment
  Future<bool> recordFlexiblePayment({
    required int studentId,
    required double amount,
    required String paymentMethod,
    required DateTime paymentDate,
    String? transactionId,
    String? remarks,
    List<Map<String, dynamic>>? allocations,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.recordFlexiblePayment(
        studentId: studentId,
        amount: amount,
        paymentMethod: paymentMethod,
        paymentDate: paymentDate,
        transactionId: transactionId,
        remarks: remarks,
        allocations: allocations,
      );
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load student ledger
  Future<void> loadStudentLedger({
    required int studentId,
    int? sessionId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _ledger = await _repository.getStudentFeeLedger(
        studentId: studentId,
        sessionId: sessionId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _ledger = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load invoices
  Future<void> loadInvoices({
    int? studentId,
    int? classId,
    String? status,
    int? sessionId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _invoices = await _repository.getInvoices(
        studentId: studentId,
        classId: classId,
        status: status,
        sessionId: sessionId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _invoices = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Generate invoice
  Future<bool> generateInvoice({
    required int studentId,
    required int feeTypeId,
    required double amount,
    double discount = 0,
    DateTime? dueDate,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.generateInvoice(
        studentId: studentId,
        feeTypeId: feeTypeId,
        amount: amount,
        discount: discount,
        dueDate: dueDate,
      );
      if (success) {
        await loadInvoices();
      }
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load payments
  Future<void> loadPayments({
    int? studentId,
    int? invoiceId,
    DateTime? startDate,
    DateTime? endDate,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _payments = await _repository.getPayments(
        studentId: studentId,
        invoiceId: invoiceId,
        startDate: startDate,
        endDate: endDate,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _payments = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Record payment
  Future<bool> recordPayment({
    required int invoiceId,
    required double amount,
    required String paymentMethod,
    required DateTime paymentDate,
    String? transactionId,
    String? remarks,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.recordPayment(
        invoiceId: invoiceId,
        amount: amount,
        paymentMethod: paymentMethod,
        paymentDate: paymentDate,
        transactionId: transactionId,
        remarks: remarks,
      );
      if (success) {
        await loadPayments();
        await loadInvoices();
      }
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load defaulters
  Future<void> loadDefaulters({
    int? classId,
    int? feeTypeId,
    int? sessionId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _defaulters = await _repository.getDefaulters(
        classId: classId,
        feeTypeId: feeTypeId,
        sessionId: sessionId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _defaulters = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load income
  Future<void> loadIncome({
    int? branchId,
    DateTime? startDate,
    DateTime? endDate,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _income = await _repository.getIncome(
        branchId: branchId,
        startDate: startDate,
        endDate: endDate,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _income = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Add income
  Future<bool> addIncome({
    int? branchId,
    required String incomeCategory,
    required double amount,
    required DateTime incomeDate,
    String? description,
    String? paymentMethod,
    String? referenceNo,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addIncome(
        branchId: branchId,
        incomeCategory: incomeCategory,
        amount: amount,
        incomeDate: incomeDate,
        description: description,
        paymentMethod: paymentMethod,
        referenceNo: referenceNo,
      );
      if (success) {
        await loadIncome();
      }
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load expenses
  Future<void> loadExpenses({
    int? branchId,
    DateTime? startDate,
    DateTime? endDate,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _expenses = await _repository.getExpenses(
        branchId: branchId,
        startDate: startDate,
        endDate: endDate,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _expenses = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Add expense
  Future<bool> addExpense({
    int? branchId,
    required String expenseCategory,
    required double amount,
    required DateTime expenseDate,
    String? description,
    String? paymentMethod,
    String? referenceNo,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final success = await _repository.addExpense(
        branchId: branchId,
        expenseCategory: expenseCategory,
        amount: amount,
        expenseDate: expenseDate,
        description: description,
        paymentMethod: paymentMethod,
        referenceNo: referenceNo,
      );
      if (success) {
        await loadExpenses();
      }
      return success;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Load finance report
  Future<void> loadFinanceReport({
    DateTime? startDate,
    DateTime? endDate,
    int? branchId,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _financeReport = await _repository.getFinanceReport(
        startDate: startDate,
        endDate: endDate,
        branchId: branchId,
      );
      _error = null;
    } catch (e) {
      _error = e.toString().replaceAll('Exception: ', '');
      _financeReport = null;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}

