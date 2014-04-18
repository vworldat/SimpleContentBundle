
jQuery(document).ready(function() {
    var addTypeForm = function(container) {
        var prototype = container.data('prototype');
        var index = container.children('.control-group').length;

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        var newForm = $(prototype.replace(/__name__/g, index));

        addDeleteButton(newForm);
        container.append(newForm);
    };
    
    var addDeleteButton = function(row) {
        row = $(row);
        var label = row.children('label').first();

        var deleteLink = $('<a href="#" class="delete-link">Remove</a>');
        deleteLink.click(function() {
            row.remove();

            return false;
        });

        label.append(deleteLink);
    };
    
    var addTypeLink = $('<a href="#" class="add_content_type_link">Add a content type</a>');
    
    var container = $('div.control-group.field_collection div[data-prototype]');
    container.append(addTypeLink);
    
    container.children('.control-group').each(function() {
        addDeleteButton($(this));
    });
    
    addTypeLink.click(function() {
        addTypeForm(container);
        
        return false;
    });
});
