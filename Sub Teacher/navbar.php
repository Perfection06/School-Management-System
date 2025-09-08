<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">

<style>
            /*========== GOOGLE FONTS ==========*/
            @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap");

            /*========== VARIABLES CSS ==========*/
            :root {
            --header-height: 3.5rem;
            --nav-width: 219px;

            /*========== Colors ==========*/
            --first-color: #6923D0;
            --first-color-light: #F4F0FA;
            --title-color: #19181B;
            --text-color: #58555E;
            --text-color-light: #A5A1AA;
            --body-color: #F9F6FD;
            --container-color: #FFFFFF;

            /*========== Font and typography ==========*/
            --body-font: 'Poppins', sans-serif;
            --normal-font-size: .938rem;
            --small-font-size: .75rem;
            --smaller-font-size: .75rem;

            /*========== Font weight ==========*/
            --font-medium: 500;
            --font-semi-bold: 600;

            /*========== z index ==========*/
            --z-fixed: 100;
            }

            @media screen and (min-width: 1024px) {
            :root {
                --normal-font-size: 1rem;
                --small-font-size: .875rem;
                --smaller-font-size: .813rem;
            }
            }

            /*========== BASE ==========*/
            *, ::before, ::after {
            box-sizing: border-box;
            }

            body {
            margin: var(--header-height) 0 0 0;
            padding: 1rem 1rem 0;
            font-family: var(--body-font);
            font-size: var(--normal-font-size);
            background-color: var(--body-color);
            color: var(--text-color);
            }

            h3 {
            margin: 0;
            }

            a {
            text-decoration: none;
            }



            /*========== HEADER ==========*/
            .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: var(--container-color);
            box-shadow: 0 1px 0 rgba(22, 8, 43, 0.1);
            padding: 0 1rem;
            z-index: var(--z-fixed);
            }

            .header__container {
            display: flex;            
            justify-content: center;   
            align-items: center;       
            height: 100%;              
            }

            .header__container h1 {
            margin: 0;                
            }

            /*========== NAV ==========*/
            .nav {
                position: fixed;
                top: 0;
                height: 100vh;
                padding: 1rem 1rem 0;
                background-color: var(--container-color);
                box-shadow: 1px 0 0 rgba(22, 8, 43, 0.1);
                z-index: var(--z-fixed);
                left: 0;
                width: 68px;
                transition: width 0.4s ease-in-out;
                overflow: hidden; 
            }

            .nav__container {
                height: 100%;
                display: flex;
                width: 200px;
                flex-direction: column;
                justify-content: space-between;
                padding-bottom: 3rem;
                padding-right: 1rem;
                overflow: auto;
                scrollbar-width: none;
                /* For mozilla */
            }

            /* Invisible hover-sensitive area */
            .nav::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                width: 20px; /* The area where the cursor triggers expansion */
                background: transparent;
                z-index: var(--z-fixed);
                pointer-events: all;
            }

            /* Expand the navbar on hover */
            .nav:hover {
                width: var(--nav-width); /* Full width defined in variables */
            }


            /* For Google Chrome and others */
            .nav__container::-webkit-scrollbar {
            display: none;
            }

            .nav__logo {
            font-weight: var(--font-semi-bold);
            margin-bottom: 2.5rem;
            }

            .nav__list, 
            .nav__items {
            display: grid;
            }

            .nav__list {
            row-gap: 2.5rem;
            }

            .nav__items {
            row-gap: 1.5rem;
            }

            .nav__subtitle {
            font-size: var(--normal-font-size);
            text-transform: uppercase;
            letter-spacing: .1rem;
            color: var(--text-color-light);
            }

            .nav__link {
            display: flex;
            align-items: center;
            color: var(--text-color);
            }

            .nav__link:hover {
            color: var(--first-color);
            }

            .nav__icon {
            font-size: 1.2rem;
            margin-right: .5rem;
            }

            .nav__name {
            font-size: var(--small-font-size);
            font-weight: var(--font-medium);
            white-space: nowrap;
            }

            .nav__logout {
            margin-top: 5rem;
            }

            /* Dropdown */
            .nav__dropdown {
            overflow: hidden;
            max-height: 21px;
            transition: .4s ease-in-out;
            }

            .nav__dropdown-collapse {
            background-color: var(--first-color-light);
            border-radius: .25rem;
            margin-top: 1rem;
            }

            .nav__dropdown-content {
            display: grid;
            row-gap: .5rem;
            padding: .75rem 2.5rem .75rem 1.8rem;
            }

            .nav__dropdown-item {
            font-size: var(--smaller-font-size);
            font-weight: var(--font-medium);
            color: var(--text-color);
            }

            .nav__dropdown-item:hover {
            color: var(--first-color);
            }

            .nav__dropdown-icon {
            margin-left: auto;
            transition: .4s;
            }

            /* Show dropdown collapse */
            .nav__dropdown:hover {
            max-height: 100rem;
            }

            /* Rotate icon arrow */
            .nav__dropdown:hover .nav__dropdown-icon {
            transform: rotate(180deg);
            }

            /*===== Show menu =====*/
            .show-menu {
            left: 0;
            }

            /*===== Active link =====*/
            .active {
            color: var(--first-color);
            }

            /* ========== MEDIA QUERIES ==========*/
            /* For small devices reduce search*/
            @media screen and (max-width: 320px) {
            .header__search {
                width: 70%;
            }
            }

            @media screen and (min-width: 768px) {
            body {
                padding: 1rem 3rem 0 6rem;
            }
            .header {
                padding: 0 3rem 0 6rem;
            }
            .header__container {
                height: calc(var(--header-height) + .5rem);
            }
            .header__search {
                width: 300px;
                padding: .55rem .75rem;
            }
            .header__toggle {
                display: none;
            }
            .header__logo {
                display: block;
            }
            .header__img {
                width: 40px;
                height: 40px;
                order: 1;
            }
            .nav {
                left: 0;
                padding: 1.2rem 1.5rem 0;
                width: 68px; /* Reduced navbar */
            }
            .nav__items {
                row-gap: 1.7rem;
            }
            .nav__icon {
                font-size: 1.3rem;
            }

            /* Element opacity */
            .nav__logo-name, 
            .nav__name, 
            .nav__subtitle, 
            .nav__dropdown-icon {
                opacity: 0;
                transition: .3s;
            }
            
            
            /* Navbar expanded */
            .nav:hover {
                width: var(--nav-width);
            }
            
            /* Visible elements */
            .nav:hover .nav__logo-name {
                opacity: 1;
            }
            .nav:hover .nav__subtitle {
                opacity: 1;
            }
            .nav:hover .nav__name {
                opacity: 1;
            }
            .nav:hover .nav__dropdown-icon {
                opacity: 1;
            }
            }

        </style>


<div class="nav" id="navbar">
            <nav class="nav__container">
                <div>
                    <a href="#" class="nav__link nav__logo">
                        <i class='bx bxs-school nav__icon' ></i>
                        <span class="nav__logo-name">Reliance</span>
                    </a>
    
                    <div class="nav__list">
                        <div class="nav__items">
                            <h3 class="nav__subtitle">System</h3>
    
                            <a href="./Sub_Teacher_Dashboard.php" class="nav__link active">
                                <i class='bx bx-home nav__icon' ></i>
                                <span class="nav__name">Dashboard</span>
                            </a>
                            

                            <a href="./profile.php" class="nav__link active">
                                <i class='bx bx-user nav__icon' ></i>
                                <span class="nav__name">Profile</span>
                            </a>


                            <div class="nav__dropdown">
                                <a href="#" class="nav__link">
                                    <i class='bx bx-user nav__icon' ></i>
                                    <span class="nav__name">Message</span>
                                    <i class='bx bx-chevron-down nav__icon nav__dropdown-icon'></i>
                                </a>

                                <div class="nav__dropdown-collapse">
                                    <div class="nav__dropdown-content">
                                        <a href="./message.php" class="nav__dropdown-item">Send Message</a>
                                        <a href="./view_messages.php" class="nav__dropdown-item">View Messages</a>
                                    </div>
                                </div>
                            </div>

                            <div class="nav__dropdown">
                                <a href="#" class="nav__link">
                                    <i class='bx bxs-graduation nav__icon' ></i>
                                    <span class="nav__name">Exams</span>
                                    <i class='bx bx-chevron-down nav__icon nav__dropdown-icon'></i>
                                </a>

                                <div class="nav__dropdown-collapse">
                                    <div class="nav__dropdown-content">
                                        <a href="./tests.php" class="nav__dropdown-item">Create Test</a>
                                        <a href="./test_marks.php" class="nav__dropdown-item">Test Marks</a>
                                        <a href="./view_exam_timetable.php" class="nav__dropdown-item">Exam Timetable</a>
                                        <a href="./marks.php" class="nav__dropdown-item">Update Marks</a>
                                        <a href="./view_results.php" class="nav__dropdown-item">Results</a>
                                    </div>
                                </div>
                            </div>

                            <a href="./assignment.php" class="nav__link active">
                                <i class='bx bx-user nav__icon' ></i>
                                <span class="nav__name">Uploads</span>
                            </a>

                            <a href="./notice.php" class="nav__link active">
                                <i class='bx bx-user nav__icon' ></i>
                                <span class="nav__name">Notice</span>
                            </a>

                            <a href="./chapters.php" class="nav__link active">
                                <i class='bx bx-user nav__icon' ></i>
                                <span class="nav__name">Chapter</span>
                            </a>

                            <a href="./subject_feedback.php" class="nav__link active">
                                <i class='bx bx-user nav__icon' ></i>
                                <span class="nav__name">Class Feedback</span>
                            </a>
                            

                <a href="../login.php" class="nav__link nav__logout">
                    <i class='bx bx-log-out nav__icon' ></i>
                    <span class="nav__name">Log Out</span>
                </a>
            </nav>
        </div>