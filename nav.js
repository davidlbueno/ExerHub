const navItems = [
    { title: 'Method', href: '#' },
    { title: 'Progressions', href: '#' },
    { title: 'Exercises', href: 'exercises.php' },
    { title: 'Workouts', href: 'workouts.php' },
    { title: 'Log In', href: '#' },
    { title: 'Create Account', href: '#' },
  ];

  document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.sidenav');
    var instances = M.Sidenav.init(elems);

    // Generate navigation links
    const desktopNav = document.querySelector('#desktop-nav');
    const mobileNav = document.querySelector('#mobile-nav');

    navItems.forEach(item => {
        const li = document.createElement('li');
        const a = document.createElement('a');

        a.textContent = item.title;
        a.href = item.href;

        li.appendChild(a);
        desktopNav.appendChild(li.cloneNode(true));
        mobileNav.appendChild(li.cloneNode(true));
    });
  });