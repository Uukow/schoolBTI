/// Fees & Finance Models for TacliinHub
library;

class FeeType {
  final int id;
  final String feeName;
  final String feeCode;
  final String? description;

  FeeType({
    required this.id,
    required this.feeName,
    required this.feeCode,
    this.description,
  });

  factory FeeType.fromJson(Map<String, dynamic> json) {
    return FeeType(
      id: _parseInt(json['id']),
      feeName: json['fee_name'] ?? '',
      feeCode: json['fee_code'] ?? '',
      description: json['description'],
    );
  }
}

class FeeStructure {
  final int id;
  final int classId;
  final String className;
  final int feeTypeId;
  final String feeTypeName;
  final int sessionId;
  final double amount;
  final DateTime? dueDate;
  final String frequency;
  final bool isMandatory;

  FeeStructure({
    required this.id,
    required this.classId,
    required this.className,
    required this.feeTypeId,
    required this.feeTypeName,
    required this.sessionId,
    required this.amount,
    this.dueDate,
    required this.frequency,
    required this.isMandatory,
  });

  factory FeeStructure.fromJson(Map<String, dynamic> json) {
    return FeeStructure(
      id: _parseInt(json['id']),
      classId: _parseInt(json['class_id']),
      className: json['class_name'] ?? '',
      feeTypeId: _parseInt(json['fee_type_id']),
      feeTypeName: json['fee_type_name'] ?? '',
      sessionId: _parseInt(json['session_id']),
      amount: double.tryParse(json['amount']?.toString() ?? '0') ?? 0.0,
      dueDate: json['due_date'] != null ? DateTime.tryParse(json['due_date']) : null,
      frequency: json['frequency'] ?? 'Monthly',
      isMandatory: (json['is_mandatory'] ?? 1) == 1,
    );
  }
}

class MonthlyFeeAssignment {
  final int id;
  final int studentId;
  final String studentName;
  final String? studentIdNumber;
  final int classId;
  final String className;
  final int feeTypeId;
  final String feeTypeName;
  final int sessionId;
  final String month;
  final double amount;
  final double? originalAmount;
  final double discountAmount;
  final String? discountType;
  final DateTime? dueDate;
  final String status;
  final double assignedAmount;
  final double paidAmount;
  final double dueAmount;
  final int? invoiceId;

  MonthlyFeeAssignment({
    required this.id,
    required this.studentId,
    required this.studentName,
    this.studentIdNumber,
    required this.classId,
    required this.className,
    required this.feeTypeId,
    required this.feeTypeName,
    required this.sessionId,
    required this.month,
    required this.amount,
    this.originalAmount,
    required this.discountAmount,
    this.discountType,
    this.dueDate,
    required this.status,
    required this.assignedAmount,
    required this.paidAmount,
    required this.dueAmount,
    this.invoiceId,
  });

  factory MonthlyFeeAssignment.fromJson(Map<String, dynamic> json) {
    return MonthlyFeeAssignment(
      id: _parseInt(json['id']),
      studentId: _parseInt(json['student_id']),
      studentName: json['student_name'] ?? 
                   '${json['first_name'] ?? ''} ${json['last_name'] ?? ''}'.trim(),
      studentIdNumber: json['student_id_number'] ?? json['student_id'] ?? json['admission_no'],
      classId: _parseInt(json['class_id']),
      className: json['class_name'] ?? '',
      feeTypeId: _parseInt(json['fee_type_id']),
      feeTypeName: json['fee_type_name'] ?? '',
      sessionId: _parseInt(json['session_id']),
      month: json['month'] ?? '',
      amount: double.tryParse(json['amount']?.toString() ?? '0') ?? 0.0,
      originalAmount: json['original_amount'] != null 
          ? double.tryParse(json['original_amount'].toString()) 
          : null,
      discountAmount: double.tryParse(json['discount_amount']?.toString() ?? '0') ?? 0.0,
      discountType: json['discount_type'],
      dueDate: json['due_date'] != null ? DateTime.tryParse(json['due_date']) : null,
      status: json['status'] ?? 'Assigned',
      assignedAmount: double.tryParse(json['assigned_amount']?.toString() ?? '0') ?? 0.0,
      paidAmount: double.tryParse(json['paid_amount']?.toString() ?? '0') ?? 0.0,
      dueAmount: double.tryParse(json['due_amount']?.toString() ?? '0') ?? 0.0,
      invoiceId: json['invoice_id'] != null ? _parseInt(json['invoice_id']) : null,
    );
  }
}

class FeeInvoice {
  final int id;
  final String invoiceNo;
  final int studentId;
  final String studentName;
  final int sessionId;
  final double totalAmount;
  final double discount;
  final double netAmount;
  final double paidAmount;
  final double dueAmount;
  final DateTime? dueDate;
  final String status;
  final DateTime createdAt;
  final DateTime? updatedAt;
  final List<FeeInvoiceItem> items;

  FeeInvoice({
    required this.id,
    required this.invoiceNo,
    required this.studentId,
    required this.studentName,
    required this.sessionId,
    required this.totalAmount,
    required this.discount,
    required this.netAmount,
    required this.paidAmount,
    required this.dueAmount,
    this.dueDate,
    required this.status,
    required this.createdAt,
    this.updatedAt,
    required this.items,
  });

  factory FeeInvoice.fromJson(Map<String, dynamic> json) {
    return FeeInvoice(
      id: _parseInt(json['id']),
      invoiceNo: json['invoice_no'] ?? '',
      studentId: _parseInt(json['student_id']),
      studentName: json['student_name'] ?? '',
      sessionId: _parseInt(json['session_id']),
      totalAmount: double.tryParse(json['total_amount']?.toString() ?? '0') ?? 0.0,
      discount: double.tryParse(json['discount']?.toString() ?? '0') ?? 0.0,
      netAmount: double.tryParse(json['net_amount']?.toString() ?? '0') ?? 0.0,
      paidAmount: double.tryParse(json['paid_amount']?.toString() ?? '0') ?? 0.0,
      dueAmount: double.tryParse(json['due_amount']?.toString() ?? '0') ?? 0.0,
      dueDate: json['due_date'] != null ? DateTime.tryParse(json['due_date']) : null,
      status: json['status'] ?? 'Unpaid',
      createdAt: DateTime.parse(json['created_at'] ?? DateTime.now().toString()),
      updatedAt: json['updated_at'] != null ? DateTime.tryParse(json['updated_at']) : null,
      items: (json['items'] as List<dynamic>?)
          ?.map((item) => FeeInvoiceItem.fromJson(item))
          .toList() ?? [],
    );
  }
}

class FeeInvoiceItem {
  final int id;
  final int invoiceId;
  final int feeTypeId;
  final String feeTypeName;
  final double amount;
  final String? description;

  FeeInvoiceItem({
    required this.id,
    required this.invoiceId,
    required this.feeTypeId,
    required this.feeTypeName,
    required this.amount,
    this.description,
  });

  factory FeeInvoiceItem.fromJson(Map<String, dynamic> json) {
    return FeeInvoiceItem(
      id: _parseInt(json['id']),
      invoiceId: _parseInt(json['invoice_id']),
      feeTypeId: _parseInt(json['fee_type_id']),
      feeTypeName: json['fee_type_name'] ?? '',
      amount: double.tryParse(json['amount']?.toString() ?? '0') ?? 0.0,
      description: json['description'],
    );
  }
}

class FeePayment {
  final int id;
  final String receiptNo;
  final int invoiceId;
  final int studentId;
  final String studentName;
  final double amount;
  final String paymentMethod;
  final String? transactionId;
  final DateTime paymentDate;
  final String? remarks;
  final String? receivedByName;
  final DateTime createdAt;

  FeePayment({
    required this.id,
    required this.receiptNo,
    required this.invoiceId,
    required this.studentId,
    required this.studentName,
    required this.amount,
    required this.paymentMethod,
    this.transactionId,
    required this.paymentDate,
    this.remarks,
    this.receivedByName,
    required this.createdAt,
  });

  factory FeePayment.fromJson(Map<String, dynamic> json) {
    return FeePayment(
      id: _parseInt(json['id']),
      receiptNo: json['receipt_no'] ?? '',
      invoiceId: _parseInt(json['invoice_id']),
      studentId: _parseInt(json['student_id']),
      studentName: json['student_name'] ?? '',
      amount: double.tryParse(json['amount']?.toString() ?? '0') ?? 0.0,
      paymentMethod: json['payment_method'] ?? 'Cash',
      transactionId: json['transaction_id'],
      paymentDate: DateTime.parse(json['payment_date'] ?? DateTime.now().toString()),
      remarks: json['remarks'],
      receivedByName: json['received_by_name'],
      createdAt: DateTime.parse(json['created_at'] ?? DateTime.now().toString()),
    );
  }
}

class StudentFeeLedger {
  final int id;
  final int studentId;
  final String studentName;
  final int sessionId;
  final String transactionType;
  final String? referenceType;
  final int? referenceId;
  final String? description;
  final double debit;
  final double credit;
  final double balance;
  final DateTime transactionDate;
  final DateTime createdAt;

  StudentFeeLedger({
    required this.id,
    required this.studentId,
    required this.studentName,
    required this.sessionId,
    required this.transactionType,
    this.referenceType,
    this.referenceId,
    this.description,
    required this.debit,
    required this.credit,
    required this.balance,
    required this.transactionDate,
    required this.createdAt,
  });

  factory StudentFeeLedger.fromJson(Map<String, dynamic> json) {
    return StudentFeeLedger(
      id: _parseInt(json['id']),
      studentId: _parseInt(json['student_id']),
      studentName: json['student_name'] ?? '',
      sessionId: _parseInt(json['session_id']),
      transactionType: json['transaction_type'] ?? '',
      referenceType: json['reference_type'],
      referenceId: json['reference_id'] != null ? _parseInt(json['reference_id']) : null,
      description: json['description'],
      debit: double.tryParse(json['debit']?.toString() ?? '0') ?? 0.0,
      credit: double.tryParse(json['credit']?.toString() ?? '0') ?? 0.0,
      balance: double.tryParse(json['balance']?.toString() ?? '0') ?? 0.0,
      transactionDate: DateTime.parse(json['transaction_date'] ?? DateTime.now().toString()),
      createdAt: DateTime.parse(json['created_at'] ?? DateTime.now().toString()),
    );
  }
}

class Income {
  final int id;
  final int? branchId;
  final String branchName;
  final String incomeCategory;
  final double amount;
  final DateTime incomeDate;
  final String? description;
  final String? paymentMethod;
  final String? referenceNo;
  final String? recordedByName;
  final DateTime createdAt;

  Income({
    required this.id,
    this.branchId,
    required this.branchName,
    required this.incomeCategory,
    required this.amount,
    required this.incomeDate,
    this.description,
    this.paymentMethod,
    this.referenceNo,
    this.recordedByName,
    required this.createdAt,
  });

  factory Income.fromJson(Map<String, dynamic> json) {
    return Income(
      id: _parseInt(json['id']),
      branchId: json['branch_id'] != null ? _parseInt(json['branch_id']) : null,
      branchName: json['branch_name'] ?? '',
      incomeCategory: json['income_category'] ?? '',
      amount: double.tryParse(json['amount']?.toString() ?? '0') ?? 0.0,
      incomeDate: DateTime.parse(json['income_date'] ?? DateTime.now().toString()),
      description: json['description'],
      paymentMethod: json['payment_method'],
      referenceNo: json['reference_no'],
      recordedByName: json['recorded_by_name'],
      createdAt: DateTime.parse(json['created_at'] ?? DateTime.now().toString()),
    );
  }
}

class Expense {
  final int id;
  final int? branchId;
  final String branchName;
  final String expenseCategory;
  final double amount;
  final DateTime expenseDate;
  final String? description;
  final String? paymentMethod;
  final String? referenceNo;
  final String? receiptFile;
  final String? approvedByName;
  final String? recordedByName;
  final DateTime createdAt;

  Expense({
    required this.id,
    this.branchId,
    required this.branchName,
    required this.expenseCategory,
    required this.amount,
    required this.expenseDate,
    this.description,
    this.paymentMethod,
    this.referenceNo,
    this.receiptFile,
    this.approvedByName,
    this.recordedByName,
    required this.createdAt,
  });

  factory Expense.fromJson(Map<String, dynamic> json) {
    return Expense(
      id: _parseInt(json['id']),
      branchId: json['branch_id'] != null ? _parseInt(json['branch_id']) : null,
      branchName: json['branch_name'] ?? '',
      expenseCategory: json['expense_category'] ?? '',
      amount: double.tryParse(json['amount']?.toString() ?? '0') ?? 0.0,
      expenseDate: DateTime.parse(json['expense_date'] ?? DateTime.now().toString()),
      description: json['description'],
      paymentMethod: json['payment_method'],
      referenceNo: json['reference_no'],
      receiptFile: json['receipt_file'],
      approvedByName: json['approved_by_name'],
      recordedByName: json['recorded_by_name'],
      createdAt: DateTime.parse(json['created_at'] ?? DateTime.now().toString()),
    );
  }
}

class FinanceReport {
  final double totalIncome;
  final double totalExpenses;
  final double netProfit;
  final double totalFeeCollection;
  final double pendingPayments;
  final int totalInvoices;
  final int paidInvoices;
  final int overdueInvoices;
  final List<MonthlySummary> monthlySummary;
  final List<CategorySummary> incomeByCategory;
  final List<CategorySummary> expensesByCategory;

  FinanceReport({
    required this.totalIncome,
    required this.totalExpenses,
    required this.netProfit,
    required this.totalFeeCollection,
    required this.pendingPayments,
    required this.totalInvoices,
    required this.paidInvoices,
    required this.overdueInvoices,
    required this.monthlySummary,
    required this.incomeByCategory,
    required this.expensesByCategory,
  });

  factory FinanceReport.fromJson(Map<String, dynamic> json) {
    return FinanceReport(
      totalIncome: double.tryParse(json['total_income']?.toString() ?? '0') ?? 0.0,
      totalExpenses: double.tryParse(json['total_expenses']?.toString() ?? '0') ?? 0.0,
      netProfit: double.tryParse(json['net_profit']?.toString() ?? '0') ?? 0.0,
      totalFeeCollection: double.tryParse(json['total_fee_collection']?.toString() ?? '0') ?? 0.0,
      pendingPayments: double.tryParse(json['pending_payments']?.toString() ?? '0') ?? 0.0,
      totalInvoices: _parseInt(json['total_invoices']),
      paidInvoices: _parseInt(json['paid_invoices']),
      overdueInvoices: _parseInt(json['overdue_invoices']),
      monthlySummary: (json['monthly_summary'] as List<dynamic>?)
          ?.map((m) => MonthlySummary.fromJson(m))
          .toList() ?? [],
      incomeByCategory: (json['income_by_category'] as List<dynamic>?)
          ?.map((c) => CategorySummary.fromJson(c))
          .toList() ?? [],
      expensesByCategory: (json['expenses_by_category'] as List<dynamic>?)
          ?.map((c) => CategorySummary.fromJson(c))
          .toList() ?? [],
    );
  }
}

class MonthlySummary {
  final String month;
  final double income;
  final double expenses;
  final double profit;

  MonthlySummary({
    required this.month,
    required this.income,
    required this.expenses,
    required this.profit,
  });

  factory MonthlySummary.fromJson(Map<String, dynamic> json) {
    return MonthlySummary(
      month: json['month'] ?? '',
      income: double.tryParse(json['income']?.toString() ?? '0') ?? 0.0,
      expenses: double.tryParse(json['expenses']?.toString() ?? '0') ?? 0.0,
      profit: double.tryParse(json['profit']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class CategorySummary {
  final String category;
  final double amount;

  CategorySummary({
    required this.category,
    required this.amount,
  });

  factory CategorySummary.fromJson(Map<String, dynamic> json) {
    return CategorySummary(
      category: json['category'] ?? '',
      amount: double.tryParse(json['amount']?.toString() ?? '0') ?? 0.0,
    );
  }
}

/// Helper function to parse int
int _parseInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is String) return int.tryParse(value) ?? 0;
  return 0;
}

