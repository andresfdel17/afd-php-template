<!DOCTYPE html>

<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$title}}</title>
    <meta name="title" content="Vmers Colombia">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link rel="shortcut icon" href="{{ config()->APP_URL }}/Assets/Public?file=img/icon.ico&type=img" sizes="100x100">
    <link href="{{ config()->APP_URL }}/Assets/Public/?file=css/AdminPages/Maintenance/reset.min.css&type=css" rel="stylesheet">
    <link href="{{ config()->APP_URL }}/Assets/Public/?file=css/AdminPages/Maintenance/styles.css&type=css" rel="stylesheet">
</head>

<body>
  <!-- partial:index.partial.html -->
<main>
    <section class="advice">
      <h1 class="advice__title">{{text("maintenance-page-title")}}</h1>
      <p class="advice__description"><span><</span> {{mb_strtolower(text("maintenance"))}} <span>/></span> {{text("maintenance-page-text")}}</p>
    </section>
    <section class="city-stuff">
      <ul class="skyscrappers__list">
        <li class="skyscrapper__item skyscrapper-1"></li>
        <li class="skyscrapper__item skyscrapper-2"></li>
        <li class="skyscrapper__item skyscrapper-3"></li>
        <li class="skyscrapper__item skyscrapper-4"></li>
        <li class="skyscrapper__item skyscrapper-5"></li>
      </ul>
      <ul class="tree__container">
        <li class="tree__list">
          <ul class="tree__item tree-1">
            <li class="tree__trunk"></li>
            <li class="tree__leaves"></li>
          </ul>
          <ul class="tree__item tree-2">
            <li class="tree__trunk"></li>
            <li class="tree__leaves"></li>
          </ul>
          <ul class="tree__item tree-3">
            <li class="tree__trunk"></li>
            <li class="tree__leaves"></li>
          </ul>  
          <ul class="tree__item tree-4">
            <li class="tree__trunk"></li>
            <li class="tree__leaves"></li>
          </ul>  
          <ul class="tree__item tree-5">
            <li class="tree__trunk"></li>
            <li class="tree__leaves"></li>
          </ul>  
          <ul class="tree__item tree-6">
            <li class="tree__trunk"></li>
            <li class="tree__leaves"></li>
          </ul>  
          <ul class="tree__item tree-7">
            <li class="tree__trunk"></li>
            <li class="tree__leaves"></li>
          </ul>  
          <ul class="tree__item tree-8">
            <li class="tree__trunk"></li>
            <li class="tree__leaves"></li>
          </ul>  
        </li>
      </ul>
      <ul class="crane__list crane-1">
        <li class="crane__item crane-cable crane-cable-1"></li>
        <li class="crane__item crane-cable crane-cable-2"></li>
        <li class="crane__item crane-cable crane-cable-3"></li>
        <li class="crane__item crane-stand"></li>
        <li class="crane__item crane-weight"></li>
        <li class="crane__item crane-cabin"></li>
        <li class="crane__item crane-arm"></li>
      </ul>
      <ul class="crane__list crane-2">
        <li class="crane__item crane-cable crane-cable-1"></li>
        <li class="crane__item crane-cable crane-cable-2"></li>
        <li class="crane__item crane-cable crane-cable-3"></li>
        <li class="crane__item crane-stand"></li>
        <li class="crane__item crane-weight"></li>
        <li class="crane__item crane-cabin"></li>
        <li class="crane__item crane-arm"></li>
      </ul>
      <ul class="crane__list crane-3">
        <li class="crane__item crane-cable crane-cable-1"></li>
        <li class="crane__item crane-cable crane-cable-2"></li>
        <li class="crane__item crane-cable crane-cable-3"></li>
        <li class="crane__item crane-stand"></li>
        <li class="crane__item crane-weight"></li>
        <li class="crane__item crane-cabin"></li>
        <li class="crane__item crane-arm"></li>
      </ul>
    </section>
  </main>
  <!-- partial -->
</body>

</html>