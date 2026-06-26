import 'dart:convert';

class StudyMaterial {
  final int id;
  final String title;
  final String description;
  final String? fileUrl;
  final String? fileType;
  final int? fileSize;
  final int? classId;
  final String? className;
  final int? subjectId;
  final String? subjectName;
  final String uploadedBy;
  final String uploadedAt;
  final String? tags;

  StudyMaterial({
    required this.id,
    required this.title,
    required this.description,
    this.fileUrl,
    this.fileType,
    this.fileSize,
    this.classId,
    this.className,
    this.subjectId,
    this.subjectName,
    required this.uploadedBy,
    required this.uploadedAt,
    this.tags,
  });

  factory StudyMaterial.fromJson(Map<String, dynamic> json) {
    return StudyMaterial(
      id: json['id'] ?? 0,
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      fileUrl: json['file_url'] ?? json['fileUrl'],
      fileType: json['file_type'] ?? json['fileType'],
      fileSize: json['file_size'] ?? json['fileSize'],
      classId: json['class_id'] ?? json['classId'],
      className: json['class_name'] ?? json['className'],
      subjectId: json['subject_id'] ?? json['subjectId'],
      subjectName: json['subject_name'] ?? json['subjectName'],
      uploadedBy: json['uploaded_by'] ?? json['uploadedBy'] ?? '',
      uploadedAt: json['uploaded_at'] ?? json['uploadedAt'] ?? '',
      tags: json['tags'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'file_url': fileUrl,
      'file_type': fileType,
      'file_size': fileSize,
      'class_id': classId,
      'class_name': className,
      'subject_id': subjectId,
      'subject_name': subjectName,
      'uploaded_by': uploadedBy,
      'uploaded_at': uploadedAt,
      'tags': tags,
    };
  }
}

class Assignment {
  final int id;
  final String title;
  final String description;
  final int classId;
  final String className;
  final int? subjectId;
  final String? subjectName;
  final String dueDate;
  final double? maxMarks;
  final String status;
  final String createdBy;
  final String createdAt;
  final int? submissionCount;

  Assignment({
    required this.id,
    required this.title,
    required this.description,
    required this.classId,
    required this.className,
    this.subjectId,
    this.subjectName,
    required this.dueDate,
    this.maxMarks,
    required this.status,
    required this.createdBy,
    required this.createdAt,
    this.submissionCount,
  });

  factory Assignment.fromJson(Map<String, dynamic> json) {
    return Assignment(
      id: json['id'] ?? 0,
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      classId: json['class_id'] ?? json['classId'] ?? 0,
      className: json['class_name'] ?? json['className'] ?? '',
      subjectId: json['subject_id'] ?? json['subjectId'],
      subjectName: json['subject_name'] ?? json['subjectName'],
      dueDate: json['due_date'] ?? json['dueDate'] ?? '',
      maxMarks: json['max_marks'] != null ? (json['max_marks'] is int ? (json['max_marks'] as int).toDouble() : json['max_marks']) : null,
      status: json['status'] ?? 'Active',
      createdBy: json['created_by'] ?? json['createdBy'] ?? '',
      createdAt: json['created_at'] ?? json['createdAt'] ?? '',
      submissionCount: json['submission_count'] ?? json['submissionCount'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'class_id': classId,
      'class_name': className,
      'subject_id': subjectId,
      'subject_name': subjectName,
      'due_date': dueDate,
      'max_marks': maxMarks,
      'status': status,
      'created_by': createdBy,
      'created_at': createdAt,
      'submission_count': submissionCount,
    };
  }
}

class Quiz {
  final int id;
  final String title;
  final String description;
  final int classId;
  final String className;
  final int? subjectId;
  final String? subjectName;
  final int durationMinutes;
  final double totalMarks;
  final int questionCount;
  final String startDate;
  final String? endDate;
  final String status;
  final String createdBy;
  final String createdAt;
  final int? attemptCount;

  Quiz({
    required this.id,
    required this.title,
    required this.description,
    required this.classId,
    required this.className,
    this.subjectId,
    this.subjectName,
    required this.durationMinutes,
    required this.totalMarks,
    required this.questionCount,
    required this.startDate,
    this.endDate,
    required this.status,
    required this.createdBy,
    required this.createdAt,
    this.attemptCount,
  });

  factory Quiz.fromJson(Map<String, dynamic> json) {
    return Quiz(
      id: json['id'] ?? 0,
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      classId: json['class_id'] ?? json['classId'] ?? 0,
      className: json['class_name'] ?? json['className'] ?? '',
      subjectId: json['subject_id'] ?? json['subjectId'],
      subjectName: json['subject_name'] ?? json['subjectName'],
      durationMinutes: json['duration_minutes'] ?? json['durationMinutes'] ?? 0,
      totalMarks: json['total_marks'] != null ? (json['total_marks'] is int ? (json['total_marks'] as int).toDouble() : json['total_marks']) : 0.0,
      questionCount: json['question_count'] ?? json['questionCount'] ?? 0,
      startDate: json['start_date'] ?? json['startDate'] ?? '',
      endDate: json['end_date'] ?? json['endDate'],
      status: json['status'] ?? 'Active',
      createdBy: json['created_by'] ?? json['createdBy'] ?? '',
      createdAt: json['created_at'] ?? json['createdAt'] ?? '',
      attemptCount: json['attempt_count'] ?? json['attemptCount'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'class_id': classId,
      'class_name': className,
      'subject_id': subjectId,
      'subject_name': subjectName,
      'duration_minutes': durationMinutes,
      'total_marks': totalMarks,
      'question_count': questionCount,
      'start_date': startDate,
      'end_date': endDate,
      'status': status,
      'created_by': createdBy,
      'created_at': createdAt,
      'attempt_count': attemptCount,
    };
  }
}

class QuizQuestion {
  final int id;
  final int quizId;
  final String question;
  final String questionType; // 'multiple_choice', 'true_false', 'short_answer'
  final List<String> options;
  final String? correctAnswer;
  final double marks;
  final int order;

  QuizQuestion({
    required this.id,
    required this.quizId,
    required this.question,
    required this.questionType,
    required this.options,
    this.correctAnswer,
    required this.marks,
    required this.order,
  });

  factory QuizQuestion.fromJson(Map<String, dynamic> json) {
    List<String> optionsList = [];
    if (json['options'] != null) {
      if (json['options'] is String) {
        optionsList = json['options'].split(',').map((e) => e.trim()).toList();
      } else if (json['options'] is List) {
        optionsList = List<String>.from(json['options']);
      }
    }

    return QuizQuestion(
      id: json['id'] ?? 0,
      quizId: json['quiz_id'] ?? json['quizId'] ?? 0,
      question: json['question'] ?? '',
      questionType: json['question_type'] ?? json['questionType'] ?? 'multiple_choice',
      options: optionsList,
      correctAnswer: json['correct_answer'] ?? json['correctAnswer'],
      marks: json['marks'] != null ? (json['marks'] is int ? (json['marks'] as int).toDouble() : json['marks']) : 0.0,
      order: json['order'] ?? json['order_number'] ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'quiz_id': quizId,
      'question': question,
      'question_type': questionType,
      'options': options.join(','),
      'correct_answer': correctAnswer,
      'marks': marks,
      'order': order,
    };
  }
}

class QuizAttempt {
  final int id;
  final int quizId;
  final String quizTitle;
  final int studentId;
  final String studentName;
  final String startedAt;
  final String? submittedAt;
  final double? score;
  final double? percentage;
  final String status; // 'in_progress', 'submitted', 'graded'
  final Map<String, String>? answers; // question_id => answer

  QuizAttempt({
    required this.id,
    required this.quizId,
    required this.quizTitle,
    required this.studentId,
    required this.studentName,
    required this.startedAt,
    this.submittedAt,
    this.score,
    this.percentage,
    required this.status,
    this.answers,
  });

  factory QuizAttempt.fromJson(Map<String, dynamic> json) {
    Map<String, String>? answersMap;
    if (json['answers'] != null) {
      if (json['answers'] is String) {
        // Parse JSON string
        try {
          answersMap = Map<String, String>.from(jsonDecode(json['answers']));
        } catch (e) {
          answersMap = {};
        }
      } else if (json['answers'] is Map) {
        answersMap = Map<String, String>.from(json['answers']);
      }
    }

    return QuizAttempt(
      id: json['id'] ?? 0,
      quizId: json['quiz_id'] ?? json['quizId'] ?? 0,
      quizTitle: json['quiz_title'] ?? json['quizTitle'] ?? '',
      studentId: json['student_id'] ?? json['studentId'] ?? 0,
      studentName: json['student_name'] ?? json['studentName'] ?? '',
      startedAt: json['started_at'] ?? json['startedAt'] ?? '',
      submittedAt: json['submitted_at'] ?? json['submittedAt'],
      score: json['score'] != null ? (json['score'] is int ? (json['score'] as int).toDouble() : json['score']) : null,
      percentage: json['percentage'] != null ? (json['percentage'] is int ? (json['percentage'] as int).toDouble() : json['percentage']) : null,
      status: json['status'] ?? 'in_progress',
      answers: answersMap,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'quiz_id': quizId,
      'quiz_title': quizTitle,
      'student_id': studentId,
      'student_name': studentName,
      'started_at': startedAt,
      'submitted_at': submittedAt,
      'score': score,
      'percentage': percentage,
      'status': status,
      'answers': answers != null ? jsonEncode(answers) : null,
    };
  }
}

