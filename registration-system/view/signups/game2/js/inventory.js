class Inventory {

    constructor() {
        this.possibleItems = {
            cheese: {
                name: 'Käse'
            },
            key: {
                name: 'Schlüssel'
            },
            salt: {
                name: 'Salzstreuer'
            }
        };

        this.foundItems = [];

        this.inventoryBucket = document.getElementById('inventory-bucket');
    }

    addItem(name) {
        var itemImg = new Image();
        itemImg.src = Environment.fapi.resolvePath('graphics/inventory/' + name + '.png');
        itemImg.id = 'inventory-item-img-' + name;
        itemImg.setAttribute('draggable', true);
        itemImg.addEventListener('dragstart', Inventory.dragDragStartHandler, false);

        var tooltip = document.createElement('span');
        tooltip.appendChild(document.createTextNode(this.possibleItems[name].name));
        tooltip.className = 'inventory-elem-tooltip';

        // wrap image in span
        var itemSpan = document.createElement('span');
        itemSpan.className = 'inventory-elem';
        itemImg.id = 'inventory-item-' + name;
        itemSpan.addEventListener('drop', Inventory.dragDropHandler, false);
        itemSpan.addEventListener('dragleave', Inventory.dragDragLeaveHandler, false);
        itemSpan.addEventListener('dragover', Inventory.dragDragOverHandler, false);

        itemSpan.appendChild(tooltip);
        itemSpan.appendChild(itemImg);

        // add to bucket
        this.inventoryBucket.appendChild(itemSpan);
    }

    mergeItems(nameA, nameB) {

    }

    static dragDragStartHandler(ev) {
        ev.dataTransfer.setData("text", ev.target.id);
    }

    static dragDropHandler(ev) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");
        ev.target.appendChild(document.getElementById(data));
    }

    static dragDragOverHandler(ev) {
        ev.preventDefault();
        ev.currentTarget.style.background = "red";
    }

    static dragDragLeaveHandler(ev) {
        ev.preventDefault();
        ev.currentTarget.style.background = "inherit";
    }
}