<?php
// load_xml_data.php - Script para cargar datos XML
session_start();

echo "<h2>🔄 Cargando datos XML...</h2>";

// Crear directorio data si no existe
if (!is_dir('data')) {
    mkdir('data', 0777, true);
    echo "✅ Directorio 'data' creado<br>";
}

// Función para crear archivo movies.xml
function createMoviesXML() {
    $moviesXML = '<?xml version="1.0" encoding="UTF-8"?>
<movies>
    <movie>
        <id>1</id>
        <title>The Dark Knight</title>
        <year>2008</year>
        <type>movie</type>
        <duration>152 min</duration>
        <synopsis>Batman debe enfrentar a su mayor enemigo en esta épica historia de heroísmo y caos.</synopsis>
        <imdb_rating>9.0</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911r6m7haRef0WH.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/hqkIcbrOHL86UncnHIsHVcVmzue.jpg</backdrop>
        <genres>
            <genre>accion</genre>
            <genre>crimen</genre>
            <genre>drama</genre>
        </genres>
        <tmdb_id>155</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/EXeTwQWrcwY</trailer_url>
    </movie>
    
    <movie>
        <id>2</id>
        <title>Inception</title>
        <year>2010</year>
        <type>movie</type>
        <duration>148 min</duration>
        <synopsis>Un ladrón que roba secretos corporativos mediante tecnología de sueños compartidos.</synopsis>
        <imdb_rating>8.8</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/s3TBrRGB1iav7gFOCNx3H31MoES.jpg</backdrop>
        <genres>
            <genre>accion</genre>
            <genre>sci-fi</genre>
            <genre>thriller</genre>
        </genres>
        <tmdb_id>27205</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/YoHD9XEInc0</trailer_url>
    </movie>
    
    <movie>
        <id>3</id>
        <title>The Godfather</title>
        <year>1972</year>
        <type>movie</type>
        <duration>175 min</duration>
        <synopsis>La saga de la familia Corleone bajo el patriarca Vito Corleone.</synopsis>
        <imdb_rating>9.2</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/3bhkrj58Vtu7enYsRolD1fZdja1.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/tmU7GeKVybMWFButWEGl2M4GeiP.jpg</backdrop>
        <genres>
            <genre>drama</genre>
            <genre>crimen</genre>
        </genres>
        <tmdb_id>238</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/sY1S34973zA</trailer_url>
    </movie>
    
    <movie>
        <id>4</id>
        <title>Pulp Fiction</title>
        <year>1994</year>
        <type>movie</type>
        <duration>154 min</duration>
        <synopsis>Historias entrelazadas de crimen y redención en Los Ángeles.</synopsis>
        <imdb_rating>8.9</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/d5iIlFn5s0ImszYzBPb8JPIfbXD.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/4cDFJr4HnXN5AdPw4AKrmLlMWdO.jpg</backdrop>
        <genres>
            <genre>crimen</genre>
            <genre>drama</genre>
        </genres>
        <tmdb_id>680</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/s7EdQ4FqbhY</trailer_url>
    </movie>
    
    <movie>
        <id>5</id>
        <title>The Matrix</title>
        <year>1999</year>
        <type>movie</type>
        <duration>136 min</duration>
        <synopsis>Un hacker descubre que la realidad que conoce es una simulación controlada por máquinas.</synopsis>
        <imdb_rating>8.7</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/fNG7i7RqMErkcqhohV2a6cV1Ehy.jpg</backdrop>
        <genres>
            <genre>accion</genre>
            <genre>sci-fi</genre>
        </genres>
        <tmdb_id>603</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/vKQi3bBA1y8</trailer_url>
    </movie>

    <movie>
        <id>6</id>
        <title>Interstellar</title>
        <year>2014</year>
        <type>movie</type>
        <duration>169 min</duration>
        <synopsis>Un grupo de exploradores viaja a través de un agujero de gusano en el espacio para asegurar el futuro de la humanidad.</synopsis>
        <imdb_rating>8.6</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/rAiYTfKGqDCRIIqo664sY9XZIvQ.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/xu9zaAevzQ5nnrsXN6JcahLnG4i.jpg</backdrop>
        <genres>
            <genre>aventura</genre>
            <genre>drama</genre>
            <genre>sci-fi</genre>
        </genres>
        <tmdb_id>157336</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/zSWdZVtXT7E</trailer_url>
    </movie>
    
    <movie>
        <id>7</id>
        <title>Fight Club</title>
        <year>1999</year>
        <type>movie</type>
        <duration>139 min</duration>
        <synopsis>Un oficinista desilusionado forma un club secreto de peleas con un vendedor de jabón carismático.</synopsis>
        <imdb_rating>8.8</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/bptfVGEQuv6vDTIMVCHjJ9Dz8PX.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/87hTDiay2N2qWyX4Ds7ybXi9h8I.jpg</backdrop>
        <genres>
            <genre>drama</genre>
        </genres>
        <tmdb_id>550</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/qtRKdVHc-cE</trailer_url>
    </movie>
    
    <movie>
        <id>8</id>
        <title>Forrest Gump</title>
        <year>1994</year>
        <type>movie</type>
        <duration>142 min</duration>
        <synopsis>La extraordinaria vida de Forrest Gump, un hombre con un corazón puro, a través de eventos históricos de EE.UU.</synopsis>
        <imdb_rating>8.8</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/saHP97rTPS5eLmrLQEcANmKrsFl.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/8zQ9X4fiRS9U2UOg4iEjoqgZZyQ.jpg</backdrop>
        <genres>
            <genre>drama</genre>
            <genre>romance</genre>
        </genres>
        <tmdb_id>13</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/bLvqoHBptjg</trailer_url>
    </movie>
    
    <movie>
        <id>9</id>
        <title>Gladiator</title>
        <year>2000</year>
        <type>movie</type>
        <duration>155 min</duration>
        <synopsis>Un general romano es traicionado y convertido en esclavo, buscando venganza como gladiador.</synopsis>
        <imdb_rating>8.5</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/ty8TGRuvJLPUmAR1H1nRIsgwvim.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/8uO0gUM8aNqYLs1OsTBQiXu0fEv.jpg</backdrop>
        <genres>
            <genre>accion</genre>
            <genre>drama</genre>
        </genres>
        <tmdb_id>98</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/owK1qxDselE</trailer_url>
    </movie>
    
    <movie>
        <id>10</id>
        <title>Parasite</title>
        <year>2019</year>
        <type>movie</type>
        <duration>132 min</duration>
        <synopsis>Una familia pobre se infiltra en una familia rica, pero el engaño conduce a consecuencias inesperadas.</synopsis>
        <imdb_rating>8.5</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/7IiTTgloJzvGI1TAYymCfbfl3vT.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/ApiBzeaa95TNYliSbQ8pJv4Fje7.jpg</backdrop>
        <genres>
            <genre>drama</genre>
            <genre>thriller</genre>
            <genre>comedia</genre>
        </genres>
        <tmdb_id>496243</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/5xH0HfJHsaY</trailer_url>
    </movie>
</movies>';

    return $moviesXML;
}

// Función para crear archivo series.xml
function createSeriesXML() {
    $seriesXML = '<?xml version="1.0" encoding="UTF-8"?>
<series>
    <show>
        <id>6</id>
        <title>Breaking Bad</title>
        <year>2008</year>
        <type>series</type>
        <duration>5 temporadas</duration>
        <synopsis>Un profesor de química se convierte en fabricante de metanfetaminas tras descubrir que tiene cáncer.</synopsis>
        <imdb_rating>9.5</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/ggFHVNu6YYI5L9pCfOacjizRGt.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/eSzpy96DwBujGFj0xMbXBcGcfxX.jpg</backdrop>
        <genres>
            <genre>drama</genre>
            <genre>crimen</genre>
            <genre>thriller</genre>
        </genres>
        <tmdb_id>1396</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/HhesaQXLuRY</trailer_url>
    </show>
    
    <show>
        <id>7</id>
        <title>Stranger Things</title>
        <year>2016</year>
        <type>series</type>
        <duration>4 temporadas</duration>
        <synopsis>Un grupo de niños descubre fuerzas sobrenaturales y experimentos secretos del gobierno.</synopsis>
        <imdb_rating>8.7</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/x2LSRK2Cm7MZhjluni1msVJ3wDF.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/56v2KjBlU4XaOv9rVYEQypROD7P.jpg</backdrop>
        <genres>
            <genre>sci-fi</genre>
            <genre>horror</genre>
            <genre>drama</genre>
        </genres>
        <tmdb_id>66732</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/b9EkMc79ZSU</trailer_url>
    </show>
    
    <show>
        <id>8</id>
        <title>Game of Thrones</title>
        <year>2011</year>
        <type>series</type>
        <duration>8 temporadas</duration>
        <synopsis>Nueve familias nobles luchan por el control del mítico continente de Westeros.</synopsis>
        <imdb_rating>9.3</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/u3bZgnGQ9T01sWNhyveQz0wH0Hl.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/suopoADq0k8YZr4dQXcU6pToj6s.jpg</backdrop>
        <genres>
            <genre>drama</genre>
            <genre>accion</genre>
            <genre>fantasia</genre>
        </genres>
        <tmdb_id>1399</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/rlR4PJn8b8I</trailer_url>
    </show>
    
    <show>
        <id>9</id>
        <title>The Office</title>
        <year>2005</year>
        <type>series</type>
        <duration>9 temporadas</duration>
        <synopsis>Un mockumentary sobre la vida cotidiana de los empleados de oficina.</synopsis>
        <imdb_rating>9.0</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/7DJKHzAi83BmQrWLrYYOqcoKfhR.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/7XhdNlNhIOXcOXpRmqNkx0rZhp2.jpg</backdrop>
        <genres>
            <genre>comedia</genre>
            <genre>drama</genre>
        </genres>
        <tmdb_id>2316</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/LHOtME2DL4g</trailer_url>
    </show>
    
    <show>
        <id>10</id>
        <title>The Crown</title>
        <year>2016</year>
        <type>series</type>
        <duration>6 temporadas</duration>
        <synopsis>La vida y el reinado de la Reina Isabel II contados a través de eventos históricos.</synopsis>
        <imdb_rating>8.6</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/1M876KPjulVwppEpldhdc8V4o68.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/wHa6KOJAoNTFLFtp7wguUJKSnju.jpg</backdrop>
        <genres>
            <genre>drama</genre>
            <genre>documentales</genre>
        </genres>
        <tmdb_id>1399</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/JWtnJjn6ng0</trailer_url>
    </show>

    <show>
        <id>11</id>
        <title>The Mandalorian</title>
        <year>2019</year>
        <type>series</type>
        <duration>3 temporadas</duration>
        <synopsis>Un cazarrecompensas solitario viaja por los confines exteriores de la galaxia, lejos de la autoridad de la Nueva República.</synopsis>
        <imdb_rating>8.7</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/sWgBv7LV2PRoQgkxwlibdGXKz1S.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/o7qi2v4uWQ3bsp7gkKnHD8QKRVX.jpg</backdrop>
        <genres>
            <genre>sci-fi</genre>
            <genre>accion</genre>
            <genre>aventura</genre>
        </genres>
        <tmdb_id>82856</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/aOC8E8z_ifw</trailer_url>
    </show>

    <show>
        <id>12</id>
        <title>Chernobyl</title>
        <year>2019</year>
        <type>series</type>
        <duration>1 temporada</duration>
        <synopsis>La historia del desastre nuclear de 1986 en Chernobyl y los valientes hombres y mujeres que se sacrificaron para salvar a Europa de una catástrofe inimaginable.</synopsis>
        <imdb_rating>9.4</imdb_rating>
        <poster>https://image.tmdb.org/t/p/w500/hlLXt2KLjqNCjk8XBdMe8VYzpwF.jpg</poster>
        <backdrop>https://image.tmdb.org/t/p/w1280/900tHlUYUkp7Ol04XFSoAaEIXcT.jpg</backdrop>
        <genres>
            <genre>drama</genre>
            <genre>historia</genre>
            <genre>thriller</genre>
        </genres>
        <tmdb_id>83216</tmdb_id>
        <trailer_url>https://www.youtube.com/embed/s9APLXM9Ei8</trailer_url>
    </show>
</series>';

    return $seriesXML;
}

// Crear archivos XML
try {
    file_put_contents('data/movies.xml', createMoviesXML());
    echo "✅ Archivo movies.xml creado exitosamente<br>";
    
    file_put_contents('data/series.xml', createSeriesXML());
    echo "✅ Archivo series.xml creado exitosamente<br>";
    
    // Verificar que los archivos se crearon correctamente
    if (file_exists('data/movies.xml') && file_exists('data/series.xml')) {
        echo "<br><h3>🎉 ¡Datos XML cargados correctamente!</h3>";
        echo "<p>Los archivos se han creado en el directorio 'data/':</p>";
        echo "<ul>";
        echo "<li>✅ data/movies.xml (" . number_format(filesize('data/movies.xml')) . " bytes)</li>";
        echo "<li>✅ data/series.xml (" . number_format(filesize('data/series.xml')) . " bytes)</li>";
        echo "</ul>";
        
        // Probar la carga de datos
        echo "<br><h3>🧪 Probando carga de datos...</h3>";
        
        if (function_exists('simplexml_load_file')) {
            $moviesTest = simplexml_load_file('data/movies.xml');
            $seriesTest = simplexml_load_file('data/series.xml');
            
            if ($moviesTest && $seriesTest) {
                echo "✅ XML válido - Se encontraron " . count($moviesTest->movie) . " películas y " . count($seriesTest->show) . " series<br>";
                echo "<br><p><strong>¡Todo listo!</strong> Ahora ve a <a href='index.php' style='color: #ff6b6b;'>la página principal</a> para ver el contenido actualizado.</p>";
            } else {
                echo "❌ Error al leer los archivos XML<br>";
            }
        } else {
            echo "❌ La función simplexml_load_file no está disponible<br>";
        }
        
    } else {
        echo "❌ Error: No se pudieron crear los archivos XML<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><p><a href='index.php' style='color: #ff6b6b; text-decoration: none; font-weight: bold;'>← Volver al inicio</a></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #0a0e27;
    color: #ffffff;
}

h2, h3 {
    color: #ff6b6b;
}

ul {
    background: rgba(255, 255, 255, 0.1);
    padding: 15px;
    border-radius: 8px;
}

a {
    color: #ff6b6b;
}
</style>