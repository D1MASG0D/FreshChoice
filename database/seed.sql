-- FreshChoice Seed Data (MariaDB / MySQL)
-- Default password for all 20 accounts: Password123!

USE freshchoice_db;

INSERT INTO store (name, address) VALUES
('FreshChoice Flagship', 'Marktstraat 12, Eindhoven'),
('FreshChoice Noord', 'Woenselse Markt 45, Eindhoven'),
('FreshChoice Zuid', 'Stratumsedijk 88, Eindhoven');

INSERT INTO user (store_id, role, first_name, last_name, email, phone_number, password_hash) VALUES
(1, 'manager',  'Emma',    'de Vries',    'emma.manager@freshchoice.nl',       '+31610000001', '$2y$10$LfhkjKMO1eB4Ig9sXGqWQ.LJxh35nK0547Hms1Swh1zZwj6DA9lF2'),
(1, 'employee', 'Lucas',   'Bakker',      'lucas@freshchoice.nl',              '+31610000002', '$2y$10$/drxUKB25QK.8dhd.MxaQ.egFS1vnFL4P4teGH0fjhdVNDtObt20u'),
(2, 'employee', 'Sophie',  'Jansen',      'sophie@freshchoice.nl',             '+31610000003', '$2y$10$K.iOuRkYWYNrF0CodLJmpu1ohymtXf/A/N0nPXhdw6XCeAzxNGG2G'),
(3, 'employee', 'Noah',    'Visser',      'noah@freshchoice.nl',               '+31610000004', '$2y$10$2GmfdhZ9/aJrkRTVJARxQeZeVVQWJQ85Xt9ve/jO2J4NhHmK.CoBK'),
(2, 'manager',  'Liam',    'van Dijk',    'liam.manager@freshchoice.nl',       '+31610000005', '$2y$10$2ji.pjMkjxPSdHWxXmmX9.n9jO/QLeTOONqotCU9WLUJEW9qumrmi'),
(3, 'manager',  'Mila',    'Smit',        'mila.manager@freshchoice.nl',       '+31610000006', '$2y$10$DdQMKUWp9qj/oArZpY1Dj.VvUpRAYRcpYyAtXTEaojcurBLfnwgpO'),
(1, 'employee', 'Ruben',   'Meijer',      'ruben.meijer@freshchoice.nl',       '+31610000007', '$2y$10$q0DRxywCPb9mfHT22dQt2Ox97kaSRMmSiIHvM4LVnx0x8jKrFvPRm'),
(1, 'employee', 'Eva',     'Mulder',      'eva.mulder@freshchoice.nl',         '+31610000008', '$2y$10$jz3wUZrHKKqs8KlbiTqjveArCG7Zwz3AI1ECDPfuePeOm7j.tGoSu'),
(1, 'employee', 'Thomas',  'de Boer',     'thomas.deboer@freshchoice.nl',      '+31610000009', '$2y$10$aQDBXPNiEKnQ0HuZMVG6puf0a1W2s6.ayk5yaMZr8BcVZ/RF5p4Ie'),
(1, 'employee', 'Julia',   'Bos',         'julia.bos@freshchoice.nl',          '+31610000010', '$2y$10$vHCo4oMcRXzbA/ANKPCE1uuJemqiZxw1aB./nzA21LUV4j6fSC6qK'),
(2, 'employee', 'Daan',    'Vos',         'daan.vos@freshchoice.nl',           '+31610000011', '$2y$10$VK/BNM/tPPK8ikjK1DGa/e/TeVnJrNoBAF39mOwUSDHaf9Vm1xO6S'),
(2, 'employee', 'Tess',    'Peters',      'tess.peters@freshchoice.nl',        '+31610000012', '$2y$10$CEttGyAfv2WGLD4J7.7sOe/ji6cvZpOfwx781BEqCLN.8wAH5DyHe'),
(2, 'employee', 'Sem',     'Hendriks',    'sem.hendriks@freshchoice.nl',       '+31610000013', '$2y$10$Mo6bkJm/D0YI1cyYcF6iX.0RZAGI0gfidnm1dOmVXCvpyr1rm68gm'),
(2, 'employee', 'Olivier', 'van Leeuwen', 'olivier.vanleeuwen@freshchoice.nl', '+31610000014', '$2y$10$tPPBSmYPBMwHpTOse4cAyeJq.mRvnj1AJwcKAXHrSMmI0ucuWO6ka'),
(3, 'employee', 'Lotte',   'Dekker',      'lotte.dekker@freshchoice.nl',       '+31610000015', '$2y$10$SxMffbGzN1UMcImG30XAr.Srn7gm57fyW1CZ6r/xRv2kqt.ymca7O'),
(3, 'employee', 'Bram',    'Brouwer',     'bram.brouwer@freshchoice.nl',       '+31610000016', '$2y$10$Cj0aUoa4Ip6eZ.CsWcORIOJkQkz1d/MG8WEXXLjVYYcMfryeCLX.6'),
(3, 'employee', 'Sara',    'Vermeer',     'sara.vermeer@freshchoice.nl',       '+31610000017', '$2y$10$OVlULpreDAK3UBF982UazOV6P9qa2Ca6fU999YiLN3K80gjKfMjxu'),
(3, 'employee', 'Finn',    'Koster',      'finn.koster@freshchoice.nl',        '+31610000018', '$2y$10$zG70twm/7j0DShblgQEH3ODECmGtd2pQNN8cdKYsIxkwnAHcywDu6'),
(3, 'employee', 'Noor',    'Hoekstra',    'noor.hoekstra@freshchoice.nl',      '+31610000019', '$2y$10$rXkPNaAFGvVloc.fKGnYDeSySJdXfEmQXTkJcFt/GUAkuI.Rx/2ri'),
(1, 'employee', 'Timo',    'Maas',        'timo.maas@freshchoice.nl',          '+31610000020', '$2y$10$.MG0BBTKlQkGXLO7l.LsbeqVTDzvg7QPvZeamNdPah.nPhOdvS3TW');

INSERT INTO shift (store_id, user_id, shift_date, start_time, end_time) VALUES
(1, 2,  '2026-11-26', '08:00:00', '16:00:00'),
(2, 3,  '2026-11-26', '12:00:00', '20:00:00'),
(3, 4,  '2026-11-27', '09:00:00', '17:00:00'),
(1, 7,  '2026-11-27', '13:00:00', '21:00:00'),
(2, 11, '2026-11-28', '08:00:00', '16:00:00'),
(3, 15, '2026-11-28', '10:00:00', '18:00:00');

INSERT INTO request (user_id, start_date, end_date, reason, status, reviewed_by) VALUES
(2,  '2026-12-01', '2026-12-03', 'Family trip',          'pending',  NULL),
(3,  '2026-12-08', '2026-12-10', 'University exam week', 'approved', 5),
(15, '2026-11-30', '2026-11-30', 'Medical appointment',  'rejected', 6);

INSERT INTO availability (user_id, weekday, start_time, end_time, status, reviewed_by) VALUES
(2,  1, '08:00:00', '18:00:00', 'approved', 1),
(3,  3, '12:00:00', '21:00:00', 'approved', 5),
(4,  5, '09:00:00', '17:00:00', 'pending',  NULL),
(11, 2, '10:00:00', '19:00:00', 'approved', 5);

INSERT INTO attendance (user_id, attendance_date, type, minutes_late, recorded_by) VALUES
(2,  '2026-11-24', 'present', 0,  1),
(3,  '2026-11-24', 'late',    15, 5),
(4,  '2026-11-24', 'sick',    0,  6),
(11, '2026-11-25', 'present', 0,  5);

INSERT INTO shift_post (shift_id, store_id, shift_date, start_time, end_time, posted_by, claimed_by, status) VALUES
(NULL, 1, '2026-11-29', '14:00:00', '21:00:00', 1, NULL, 'open'),
(2,    2, '2026-11-26', '12:00:00', '20:00:00', 3, 11,   'claimed'),
(NULL, 3, '2026-11-30', '09:00:00', '17:00:00', 6, NULL, 'open');