-- 1. 教員スケジュール管理テーブル
CREATE TABLE teacher_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_name VARCHAR(50) NOT NULL,
    day_of_week VARCHAR(10) NOT NULL, -- '月', '火'...
    am_available TINYINT(1) DEFAULT 0,
    pm_available TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_schedule (teacher_name, day_of_week)
);

-- 2. 進路相談・面談予約テーブル
CREATE TABLE consultations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_date_2 DATE NULL,
    appointment_date_3 DATE NULL,
    teacher_name VARCHAR(50),
    content TEXT,
    ai_recommendation TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. 進路希望調査票テーブル
CREATE TABLE career_surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    first_choice VARCHAR(100),
    consultation TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. 模擬面接予約テーブル
CREATE TABLE interviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    appointment_date DATE NOT NULL,
    pref_teacher_1 VARCHAR(50),
    pref_teacher_2 VARCHAR(50),
    pref_teacher_3 VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);