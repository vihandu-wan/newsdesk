USE newsdesk_db;

INSERT IGNORE INTO users (student_id, name, email, contact, faculty, password_hash, role) VALUES
('26CS001', 'Kasun Perera',  'kasun@students.nsbm.ac.lk',  '0771234567', 'Faculty of Computing',   '$2y$10$amxe/BgfKyHRwFIMQU5zjOUkulTITdSNRfa2ZnXwC2X.0lUo1Y38y', 'student'),
('26BM014', 'Nimali Silva',  'nimali@students.nsbm.ac.lk', '0719876543', 'Faculty of Business',    '$2y$10$amxe/BgfKyHRwFIMQU5zjOUkulTITdSNRfa2ZnXwC2X.0lUo1Y38y', 'student'),
('26EN033', 'Ravindu Jayasinghe', 'ravindu@students.nsbm.ac.lk', NULL, 'Faculty of Engineering', '$2y$10$amxe/BgfKyHRwFIMQU5zjOUkulTITdSNRfa2ZnXwC2X.0lUo1Y38y', 'student');

SET @admin  = (SELECT id FROM users WHERE email = 'admin@nsbm.ac.lk');
SET @kasun  = (SELECT id FROM users WHERE email = 'kasun@students.nsbm.ac.lk');
SET @nimali = (SELECT id FROM users WHERE email = 'nimali@students.nsbm.ac.lk');
SET @academic = (SELECT id FROM categories WHERE name = 'Academic');
SET @events   = (SELECT id FROM categories WHERE name = 'Events');
SET @sports   = (SELECT id FROM categories WHERE name = 'Sports');
SET @general  = (SELECT id FROM categories WHERE name = 'General');

INSERT INTO articles (title, content, category_id, author_id, status, is_featured, is_breaking, published_at) VALUES
('Semester 2 final examination timetable released',
 'The Examinations Division has released the final examination timetable for Semester 2. Students are requested to check their module codes carefully and report any clashes to their faculty office before the end of this week. Admission cards can be downloaded from the student portal.',
 @academic, @admin, 'published', 1, 1, NOW() - INTERVAL 1 DAY),
('NSBM Hackathon 2026 registrations now open',
 'Teams of three to five students can now register for the annual NSBM Hackathon. This year''s theme is "Green Tech for Smart Campuses". Prizes worth LKR 500,000 are up for grabs, and mentors from leading tech companies will guide the finalists.',
 @events, @admin, 'published', 0, 1, NOW() - INTERVAL 2 DAY),
('NSBM cricket team reaches inter-university finals',
 'After a thrilling semi-final win by four wickets, the NSBM men''s cricket team has secured a place in the inter-university finals. The final will be played at the university grounds next Saturday. All students are invited to come and support the team.',
 @sports, @admin, 'published', 0, 0, NOW() - INTERVAL 3 DAY),
('New study spaces open at the library',
 'The university library has opened twenty new group study rooms on the second floor. Rooms can be booked for up to two hours through the library desk. Extended opening hours will apply during the examination period.',
 @general, @admin, 'published', 0, 0, NOW() - INTERVAL 5 DAY),
('Guest lecture: AI in Sri Lankan industry',
 'The Faculty of Computing will host a guest lecture on how artificial intelligence is transforming Sri Lankan businesses. The session will cover real case studies and career paths in AI. Attendance is open to all faculties.',
 @academic, @admin, 'published', 0, 0, NOW() - INTERVAL 8 DAY),
('Annual sports meet results announced',
 'The Faculty of Engineering won the overall championship at this year''s annual sports meet, followed closely by the Faculty of Business. Congratulations to all athletes and volunteers who made the event a success.',
 @sports, @kasun, 'published', 0, 0, NOW() - INTERVAL 12 DAY),
('Green Campus clean-up day this weekend',
 'The Environmental Society invites all students to join the Green Campus clean-up day. Gloves and refreshments will be provided. Volunteers will receive community service hours.',
 @events, @nimali, 'published', 0, 0, NOW() - INTERVAL 15 DAY),
('Draft: convocation arrangements',
 'Details about the upcoming convocation will be shared soon.', @general, @admin, 'draft', 0, 0, NULL);

INSERT INTO article_submissions (title, content, category_id, author_id, status) VALUES
('Robotics club wins national competition',
 'Our robotics club won first place at the national robotics challenge held in Colombo last weekend, competing against 25 universities. The winning robot was designed to sort recyclable waste automatically.',
 @events, @kasun, 'pending'),
('Business faculty case-study competition winners',
 'Team "Insight" from the Faculty of Business won the inter-faculty case-study competition with their analysis of sustainable supply chains in the tea industry.',
 @academic, @nimali, 'pending');

INSERT INTO announcements (title, content, author_id, approved_by, status, is_pinned, published_at) VALUES
('Campus closed on Poya day', 'The university will be closed on the upcoming Poya day. Lectures will resume the following day as scheduled.', @admin, @admin, 'published', 1, NOW() - INTERVAL 1 DAY),
('Shuttle bus schedule updated', 'The campus shuttle bus schedule has been updated. Buses will now leave Homagama town every 20 minutes during peak hours.', @admin, @admin, 'published', 0, NOW() - INTERVAL 4 DAY);

INSERT IGNORE INTO subscriptions (user_id, category_id) VALUES (@kasun, @sports), (@kasun, @events), (@nimali, @academic);

SET @a1 = (SELECT id FROM articles WHERE title LIKE 'Semester 2%');
SET @a2 = (SELECT id FROM articles WHERE title LIKE 'NSBM Hackathon%');
SET @a3 = (SELECT id FROM articles WHERE title LIKE 'NSBM cricket%');

INSERT INTO comments (article_id, user_id, content, status) VALUES
(@a1, @kasun, 'Thanks for sharing! Is the timetable the same for repeat students?', 'approved'),
(@a2, @nimali, 'Can first-year students also take part?', 'approved'),
(@a3, @kasun, 'Go NSBM! See you all at the grounds 🏏', 'approved'),
(@a3, @nimali, 'This comment is waiting for moderation.', 'pending');

INSERT IGNORE INTO bookmarks (user_id, article_id) VALUES (@kasun, @a1), (@kasun, @a2), (@nimali, @a1);

INSERT INTO article_views (article_id, user_id, viewed_at)
SELECT a.id, IF(n.n % 3 = 0, @kasun, IF(n.n % 3 = 1, @nimali, NULL)), NOW() - INTERVAL (n.n % 14) DAY - INTERVAL n.n HOUR
FROM articles a
JOIN (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7
      UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) n
  ON n.n <= 3 + (a.id % 9)
WHERE a.status = 'published';
