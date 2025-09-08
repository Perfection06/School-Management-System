<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>School Management System — README</title>
  <style>
    :root {
      --bg: #0b1020; /* deep navy */
      --card: #121933;
      --muted: #8fa0ff;
      --accent: #ff375f; /* lively pink-red */
      --text: #f5f7ff;
      --sub: #c7ceff;
      --border: rgba(255,255,255,0.08);
      --chip: #1a2247;
      --green: #28c76f;
      --yellow: #ffc107;
      --blue: #3b82f6;
    }
    * { box-sizing: border-box; }
    html, body {
      margin: 0;
      padding: 0;
      background: linear-gradient(180deg, #0b1020 0%, #0b1228 100%);
      color: var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif;
      line-height: 1.5;
    }
    a { color: var(--muted); text-decoration: none; }
    a:hover { color: var(--accent); }
    main {
      max-width: 1060px;
      margin: 0 auto;
      padding: 40px 20px 100px;
    }
    header {
      display: grid;
      gap: 16px;
      margin-bottom: 32px;
      text-align: center;
    }
    h1 {
      font-size: clamp(32px, 6vw, 52px);
      font-weight: 800;
      letter-spacing: -0.5px;
      line-height: 1.1;
      margin: 0;
    }
    p.subtitle {
      color: var(--sub);
      font-size: 18px;
      max-width: 80ch;
      margin: 12px auto;
    }
    .badges {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
      margin-bottom: 16px;
    }
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 14px;
      border: 1px solid var(--border);
      background: var(--chip);
      border-radius: 999px;
      font-size: 14px;
      color: var(--sub);
    }
    .badge .dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
    }
    .dot.green { background: var(--green); }
    .dot.blue { background: var(--blue); }
    .dot.yellow { background: var(--yellow); }
    article {
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 24px;
      margin-bottom: 20px;
    }
    article h2 {
      font-size: 28px;
      font-weight: 700;
      margin: 0 0 12px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    article p {
      color: var(--sub);
      margin: 0 0 12px;
    }
    .chips {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 12px;
    }
    .chip {
      background: var(--chip);
      border: 1px solid var(--border);
      padding: 6px 12px;
      border-radius: 999px;
      font-size: 13px;
      color: var(--sub);
    }
    ul.list, ol.list {
      margin: 12px 0 0;
      padding-left: 20px;
      color: var(--sub);
    }
    .list li {
      margin: 8px 0;
    }
    .grid-2 {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
      margin-bottom: 20px;
    }
    @media (min-width: 860px) {
      .grid-2 { grid-template-columns: 1fr 1fr; }
    }
    .code {
      background: #0b0f22;
      border: 1px dashed var(--border);
      border-radius: 14px;
      padding: 16px;
      overflow: auto;
      margin: 12px 0;
    }
    .code pre {
      margin: 0;
      font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
      font-size: 13px;
      color: #e5eaff;
    }
    .cta {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 16px;
      justify-content: center;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--accent);
      color: white;
      padding: 10px 16px;
      border-radius: 12px;
      font-weight: 600;
      border: 0;
      transition: transform 0.2s;
    }
    .btn:hover {
      transform: translateY(-2px);
    }
    .btn.secondary {
      background: transparent;
      border: 1px solid var(--border);
      color: var(--text);
    }
    .kbd {
      background: #0e1530;
      border: 1px solid var(--border);
      padding: 3px 8px;
      border-radius: 6px;
      font-size: 13px;
      font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    }
    .divider {
      height: 1px;
      background: var(--border);
      margin: 32px 0;
    }
    footer {
      margin-top: 32px;
      color: var(--sub);
      font-size: 14px;
      text-align: center;
    }
    .accent {
      color: var(--accent);
      font-weight: 700;
    }
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 8px;
    }
    table th {
      font-size: 13px;
      color: var(--sub);
      text-align: left;
      padding: 8px 12px;
    }
    .row {
      background: var(--chip);
      border: 1px solid var(--border);
      border-radius: 12px;
    }
    .row td {
      padding: 12px;
      font-size: 14px;
    }
    .callout {
      background: var(--chip);
      border-left: 4px solid var(--accent);
      padding: 12px 16px;
      border-radius: 8px;
      margin: 12px 0;
      color: var(--sub);
    }
  </style>
</head>
<body>
  <main>
    <header>
      <p class="badges">
        <span class="badge"><span class="dot green"></span> Active</span>
        <span class="badge"><span class="dot blue"></span> PHP 8+</span>
        <span class="badge"><span class="dot blue"></span> MySQL 8+</span>
        <span class="badge"><span class="dot yellow"></span> Role-Based Access</span>
        <span class="badge">📦 Version: 1.0.0</span>
        <span class="badge">🛡️ License: MIT</span>
      </p>
      <h1>📚 School Management System</h1>
      <p class="subtitle">A powerful, role-based platform to streamline administrative, academic, and financial workflows for schools. Supports <strong>Admin</strong>, <strong>Teacher</strong>, <strong>Sub-Teacher</strong>, <strong>Student</strong>, and <strong>Staff</strong> (e.g., Cashier, Clerk) with real-world efficiency.</p>
      <p class="cta">
        <a class="btn" href="#getting-started">🚀 Get Started</a>
        <a class="btn secondary" href="#features">✨ Explore Features</a>
      </p>
    </header>

    <article id="features">
      <h2>✨ Core Features</h2>
      <p>Streamline school operations with these robust features:</p>
      <ul class="list">
        <li><strong>🔐 Role-Based Access Control</strong>: Custom permissions for Admin, Teacher, Sub-Teacher, Student, and Staff.</li>
        <li><strong>🧑‍🎓 User Onboarding</strong>: Assign classes, subjects, or roles (e.g., Cashier, Clerk).</li>
        <li><strong>📅 Attendance Management</strong>: Track attendance by class or subject.</li>
        <li><strong>📝 Exams & Results</strong>: Create exams, record grades, and view results.</li>
        <li><strong>📚 Materials & Resources</strong>: Share class or subject-specific resources.</li>
        <li><strong>💸 Fee Management</strong>: Process payments and maintain transaction history.</li>
        <li><strong>📢 Notices & Messaging</strong>: Broadcast announcements and manage inbox/outbox.</li>
        <li><strong>🚫 Blocking & Moderation</strong>: Block users with stored reasons for transparency.</li>
        <li><strong>📊 Dashboards</strong>: Role-specific dashboards with widgets for users, stats, results, and events.</li>
      </ul>
      <p class="chips">
        <span class="chip">Authentication</span>
        <span class="chip">Class & Subject Mapping</span>
        <span class="chip">Timetables</span>
        <span class="chip">Payments</span>
        <span class="chip">Events Calendar</span>
        <span class="chip">Reporting</span>
      </p>
      <p class="callout">💡 <strong>Tip</strong>: Dashboards provide quick insights tailored to each role, enhancing productivity.</p>
    </article>

    <article id="tech-stack">
      <h2>🧩 Tech Stack</h2>
      <p>Built with modern, reliable technologies:</p>
      <ul class="list">
        <li><strong>Backend</strong>: PHP 8+</li>
        <li><strong>Database</strong>: MySQL 8+ / MariaDB 10.4+ (via XAMPP/LAMPP)</li>
        <li><strong>Frontend</strong>: HTML5, CSS3, JavaScript</li>
        <li><strong>Server</strong>: Apache (XAMPP)</li>
        <li><strong>APIs</strong>: REST-ish endpoints with PDO/MySQLi and session-based authentication</li>
      </ul>
      <p class="chips">
        <span class="chip">PDO/MySQLi</span>
        <span class="chip">Session Auth</span>
        <span class="chip">REST-ish Endpoints</span>
      </p>
    </article>

    <p class="grid-2">
      <article id="roles">
        <h2>👥 User Roles & Permissions</h2>
        <table role="table" aria-label="Roles and capabilities">
          <thead>
            <tr>
              <th>Role</th>
              <th>Core Capabilities</th>
            </tr>
          </thead>
          <tbody>
            <tr class="row">
              <td><strong>Admin</strong></td>
              <td>🌟 Full control: Manage users, assign classes/subjects/roles, block/unblock users, manage timetables, notices, and settings.</td>
            </tr>
            <tr class="row">
              <td><strong>Teacher</strong></td>
              <td>📚 Manage assigned classes/subjects, mark attendance, upload materials, create exams, record grades, and track student progress.</td>
            </tr>
            <tr class="row">
              <td><strong>Sub-Teacher</strong></td>
              <td>🧑‍🏫 Same academic permissions as Teacher, assisting specific classes/subjects.</td>
            </tr>
            <tr class="row">
              <td><strong>Staff</strong></td>
              <td>💼 Task-based access (e.g., Cashier handles payments, Clerk manages admissions).</td>
            </tr>
            <tr class="row">
              <td><strong>Student</strong></td>
              <td>👨‍🎓 View materials, timetables, results, notices, and submit feedback where applicable.</td>
            </tr>
          </tbody>
        </table>
      </article>

      <article id="screens">
        <h2>🖼️ Screenshots</h2>
        <p>Make your README pop by adding screenshots to <code class="kbd">assets/screens/</code>. Recommended images:</p>
        <ul class="list">
          <li>🖥️ Admin Dashboard with widgets</li>
          <li>📋 Teacher Attendance & Materials</li>
          <li>💰 Cashier Fee Management</li>
          <li>📊 Student Results View</li>
        </ul>
        <p class="chips">
          <span class="chip">assets/screens/admin-dashboard.png</span>
          <span class="chip">assets/screens/attendance.png</span>
          <span class="chip">assets/screens/fees.png</span>
        </p>
        <p class="callout">🎨 <strong>Tip</strong>: High-quality screenshots attract contributors and showcase functionality!</p>
      </article>
    </p>

    <article id="getting-started">
      <h2>🚀 Getting Started (Local Setup)</h2>
      <p>Set up the project locally with <strong>XAMPP</strong> in a few steps:</p>
      <ol class="list">
        <li>Install <a href="https://www.apachefriends.org/index.html" target="_blank" rel="noopener">XAMPP</a> and start <strong>Apache</strong> and <strong>MySQL</strong>.</li>
        <li>Clone or copy the project to <code class="kbd">htdocs/School-Management-System</code>.</li>
        <li>Create a database (e.g., <code class="kbd">sms_db</code>) in phpMyAdmin.</li>
        <li>Import the SQL schema (e.g., <code class="kbd">reliance(1).sql</code> or <code class="kbd">tms.sql</code>).</li>
        <li>Configure database credentials in <code class="kbd">database_connection.php</code>:</li>
      </ol>
      <p class="code" aria-label="DB config sample">
        <pre>
// database_connection.php
$host = '127.0.0.1';
$db   = 'sms_db';
$user = 'root';
$pass = '';
$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
        </pre>
      </p>
      <ol class="list" start="6">
        <li>Visit <code class="kbd">http://localhost/School-Management-System/login.php</code> to log in.</li>
      </ol>
      <p class="chips">
        <span class="chip">PHP 8+</span>
        <span class="chip">MySQL 8+ / MariaDB 10.4+</span>
        <span class="chip">Apache</span>
      </p>
    </article>

    <p class="grid-2">
      <article id="folder-structure">
        <h2>📁 Suggested Folder Structure</h2>
        <p class="code">
          <pre>
/ (root)
├── assets/
│   ├── css/               # Global styles
│   ├── js/                # Scripts
│   └── screens/           # README screenshots
├── Teachers/              # Teacher dashboard & pages
├── Students/              # Student portal
├── Staffs/                # Staff portal (Cashier, Clerk, etc.)
├── Resources/             # Materials and uploads
├── Sub Teacher/           # Sub-Teacher views
├── Cashier/               # Fees & payment pages
├── database_connection.php # Database config
├── login.php              # Login page
├── Admin_Dashboard.php    # Admin dashboard
└── ...                    # Other modules
          </pre>
        </p>
      </article>

      <article id="security">
        <h2>🔐 Security Notes</h2>
        <p>Ensure a secure deployment with these practices:</p>
        <ul class="list">
          <li><strong>🛡️ Prepared Statements</strong>: Use PDO/MySQLi to prevent SQL injection.</li>
          <li><strong>🔒 Password Hashing</strong>: Implement <code class="kbd">password_hash()</code> in PHP.</li>
          <li><strong>🚪 Route Protection</strong>: Secure role-based routes with session middleware.</li>
          <li><strong>📂 File Validation</strong>: Validate uploaded files and set proper permissions.</li>
          <li><strong>📜 Audit Logs</strong>: Store block reasons and moderation events.</li>
        </ul>
      </article>
    </p>

    <p class="grid-2">
      <article id="sample-users">
        <h2>🧪 Sample Users (Optional)</h2>
        <p>For testing, seed the database with these users (replace with real data):</p>
        <p class="code">
          <pre>
Admin       → admin@example.com / Admin@123
Teacher     → teacher@example.com / Teacher@123
Sub-Teacher → subteacher@example.com / Sub@123
Staff       → cashier@example.com / Cashier@123
Student     → student@example.com / Student@123
          </pre>
        </p>
      </article>

      <article id="key-modules">
        <h2>📊 Key Modules</h2>
        <p>Core functionalities for seamless operations:</p>
        <ul class="list">
          <li><strong>📅 Attendance</strong>: Track per class/subject with date filters.</li>
          <li><strong>📝 Exams & Results</strong>: Create, manage, and view exams and grades.</li>
          <li><strong>💸 Fees</strong>: Assign payments, view history, and generate reports.</li>
          <li><strong>📢 Notices & Messaging</strong>: Broadcast announcements and manage communications.</li>
          <li><strong>🗓️ Timetable & Events</strong>: Manage schedules and calendar.</li>
        </ul>
      </article>
    </p>

    <article id="roadmap">
      <h2>🛣️ Roadmap</h2>
      <p>Future enhancements to elevate the system:</p>
      <ul class="list">
        <li>📄 Exportable reports (PDF/Excel)</li>
        <li>👨‍👩‍👧 Parent portal</li>
        <li>📧 Email/SMS gateway integration</li>
        <li>📈 Role analytics & logs</li>
        <li>📱 API endpoints for mobile app</li>
      </ul>
    </article>

    <p class="grid-2">
      <article id="contributing">
        <h2>🤝 Contributing</h2>
        <p>We welcome contributions! Fork the repo and submit a pull request. Follow this flow for consistency:</p>
        <p class="code">
          <pre>
# Example contribution flow
git checkout -b feat/awesome-module
# ...code...
git commit -m "feat: add awesome module"
git push origin feat/awesome-module
          </pre>
        </p>
        <p class="callout">💡 <strong>Pro Tip</strong>: Use <a href="https://www.conventionalcommits.org/" target="_blank" rel="noopener">conventional commits</a> for clear, professional contributions.</p>
      </article>

      <article id="license">
        <h2>📜 License</h2>
        <p>Released under the <a href="https://opensource.org/licenses/MIT" target="_blank" rel="noopener"><strong>MIT License</strong></a>. Feel free to use, modify, and distribute this project.</p>
      </article>
    </p>

    <article id="contact">
      <h2>📬 Contact</h2>
      <p>Have questions or want to collaborate? Reach out:</p>
      <ul class="list">
        <li><strong>Author</strong>: Your Name</li>
        <li><strong>GitHub</strong>: <a href="#">github.com/your-username</a></li>
        <li><strong>Email</strong>: you@example.com</li>
      </ul>
    </article>

    <p class="divider"></p>
    <footer>
      <p>💡 <strong>Pro Tip</strong>: Add vibrant screenshots to <code class="kbd">assets/screens/</code> and link them in the Screenshots section. A visually appealing README attracts contributors and showcases your project’s potential!</p>
    </footer>
  </main>
</body>
</html>