document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const subjectAssignment = document.getElementById('subject_assignment');
    const classTeacherDiv = document.getElementById('class_teacher_div');
    const gradeSelect = document.getElementById('grade_select');
    const positionDiv = document.getElementById('position_div');

    roleSelect.addEventListener('change', function() {
        if (roleSelect.value === 'Teacher') {
            subjectAssignment.style.display = 'block';
            classTeacherDiv.style.display = 'block';
        } else if (roleSelect.value === 'No Class Teacher') {
            subjectAssignment.style.display = 'block';
            classTeacherDiv.style.display = 'none';
        } else {
            subjectAssignment.style.display = 'none';
            classTeacherDiv.style.display = 'none';
        }
    });

    const classTeacherCheckbox = document.getElementById('class_teacher');
    classTeacherCheckbox.addEventListener('change', function() {
        if (classTeacherCheckbox.checked) {
            gradeSelect.style.display = 'block';
            positionDiv.style.display = 'block';
        } else {
            gradeSelect.style.display = 'none';
            positionDiv.style.display = 'none';
        }
    });
});

function addMoreRoles() {
    const rolesContainer = document.getElementById('previous_roles_container');
    const roleCount = rolesContainer.getElementsByClassName('previous_role_entry').length + 1;
    
    const roleEntry = document.createElement('div');
    roleEntry.classList.add('previous_role_entry');

    roleEntry.innerHTML = `
        <label for="previous_role_${roleCount}">Previous Role:</label>
        <input type="text" id="previous_role_${roleCount}" name="previous_role[]" placeholder="e.g., Teacher, Administrator">

        <label for="previous_company_${roleCount}">Previous Company/Organization:</label>
        <input type="text" id="previous_company_${roleCount}" name="previous_company[]" placeholder="e.g., ABC School, XYZ Company">

        <label for="years_experience_${roleCount}">Total Years of Experience:</label>
        <input type="number" id="years_experience_${roleCount}" name="years_experience[]" placeholder="e.g., 5" required>
    `;

    rolesContainer.appendChild(roleEntry);
}