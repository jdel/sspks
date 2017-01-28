function toggleClass(class_name, element_ids)
{
    for (var i in element_ids) {
        if (!element_ids.hasOwnProperty(i)) continue;
        var element = document.getElementById(element_ids[i]);
        if (element.className.indexOf(class_name) != -1) {
            // class already there, remove it
            element.className = element.className.replace(class_name, '').trim();
        } else {
            // class not there, add it
            element.className += ' ' + class_name;
        }
    }
}

function toggleBeta()
{
    var input_element = document.getElementById('switch-beta');
    var elements = document.getElementsByClassName('spk-beta');
    var newState = "none";
    if (input_element.checked) {
        newState = "";
    }
    for (var i in elements) {
        if (!elements.hasOwnProperty(i)) continue;
        elements[i].style.display = newState;
    }
}

function toggleDetails(clicked_element)
{
    var card = clicked_element.parentElement.parentElement;
    var details = card.getElementsByClassName('spk-details')[0];
    console.log('Element: %o', details);
    if (details.style.display) {
        // Make details visible
        details.style.display = "";
        clicked_element.innerHTML = 'Hide Info';
    } else {
        // Hide details
        details.style.display = "none";
        clicked_element.innerHTML = 'More Info';
    }
}

function hideAllDetails()
{
    var elements = document.getElementsByClassName('spk-details');
    for (var i in elements) {
        if (!elements.hasOwnProperty(i)) continue;
        elements[i].style.display = "none";
    }
}