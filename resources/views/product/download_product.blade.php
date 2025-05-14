<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Imagen</th>
            <th width="24">Laboratorio</th>
            <th width="35">Titulo</th>
            <th width="22">Linea farmaceutica</th>
            <th>SKU</th>
            <th width="12">Precio venta</th>
            <th width="12">Stock</th>
            <th width="12">Stock vendedor</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products_export as $k => $v)
        <tr>
            <td>{{ $k + 1}}</td>
            <td>{{ $v->imagen}}</td>
            <td>{{ $v->get_laboratorio->name }}</td>
            <td>{{ $v->nombre }} {{ $v->caracteristicas }}</td>
            <td>{{ $v->get_lineaFarmaceutica->nombre }}</td>
            <td>{{ $v->sku }}</td>
            <td>{{ $v->pventa }}</td>
            <td>{{ $v->stock }}</td>
            <td>{{ $v->stock_vendedor }}</td>
        </tr>
        @endforeach
    </tbody>
</table>