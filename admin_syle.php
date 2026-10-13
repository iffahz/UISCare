* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    background: #f5f6fa;
}

/* SIDEBAR */

.sidebar {
    width: 240px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;

    background: #263238;
    color: white;

    padding: 25px 15px;
}

.sidebar-title {
    text-align: center;
    margin-bottom: 30px;
}

.sidebar-title h2 {
    font-size: 20px;
}

.sidebar-title p {
    color: #b0bec5;
    margin-top: 5px;
}

.sidebar a {
    display: block;

    color: white;
    text-decoration: none;

    padding: 14px 15px;
    margin: 6px 0;

    border-radius: 8px;
}

.sidebar a:hover {
    background: #37474f;
}

.sidebar .logout {
    background: #d32f2f;
    margin-top: 30px;
}

.sidebar .logout:hover {
    background: #b71c1c;
}


/* MAIN */

.main {
    margin-left: 240px;
    padding: 30px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    margin-bottom: 25px;
}

.header h1 {
    color: #263238;
}


/* DASHBOARD CARDS */

.cards {
    display: grid;

    grid-template-columns:
        repeat(auto-fit, minmax(200px, 1fr));

    gap: 20px;
}

.card {
    background: white;

    padding: 25px;

    border-radius: 12px;

    box-shadow:
        0 2px 10px rgba(0,0,0,0.08);
}

.card h3 {
    color: #607d8b;
    font-size: 16px;
}

.card .number {
    font-size: 32px;
    font-weight: bold;

    margin-top: 10px;

    color: #263238;
}


/* TABLE */

.table-container {
    background: white;

    padding: 20px;

    border-radius: 12px;

    margin-top: 25px;

    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 13px;

    border-bottom: 1px solid #ddd;

    text-align: left;
}

th {
    background: #eceff1;
}

tr:hover {
    background: #fafafa;
}


/* BUTTON */

.btn {
    display: inline-block;

    padding: 8px 12px;

    border-radius: 6px;

    text-decoration: none;

    color: white;

    font-size: 14px;
}

.btn-view {
    background: #1976d2;
}

.btn-edit {
    background: #f57c00;
}

.btn-delete {
    background: #d32f2f;
}

.btn-active {
    background: #388e3c;
}

.btn-inactive {
    background: #757575;
}

.btn-add {
    background: #388e3c;

    padding: 10px 15px;
}


/* STATUS */

.status {
    padding: 6px 10px;

    border-radius: 15px;

    font-size: 12px;
    font-weight: bold;
}

.active {
    background: #c8e6c9;
    color: #256029;
}

.inactive {
    background: #ffcdd2;
    color: #b71c1c;
}

.pending {
    background: #fff3cd;
    color: #856404;
}

.approved {
    background: #c8e6c9;
    color: #256029;
}

.completed {
    background: #bbdefb;
    color: #0d47a1;
}

.cancelled,
.rejected {
    background: #ffcdd2;
    color: #b71c1c;
}