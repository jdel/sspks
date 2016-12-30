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