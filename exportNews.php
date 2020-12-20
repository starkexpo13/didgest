<?php
define('ROOT_DIR', __DIR__);
define('ENV', 'Console');

spl_autoload_register(function ($classname) {
    $classname = str_replace('\\', '/', $classname);
    require_once(ROOT_DIR . "/$classname.php");
});

use Dotenv\Dotenv;

class Connection
{
    private $link;
    private $config;

    /**
     * Connection constructor.
     */
    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(ROOT_DIR);
        $this->config = $dotenv->load();

        $this->connect();
    }


    /**
     * @return \Engine\Database\Connection
     */
    public function connect()
    {
        try {
            $dns = $this->config['PDO_DRIVER'] . ":host=" . $this->config['DB_HOST'] . ";port=" . $this->config['DB_PORT'] . ";" . "dbname='" . $this->config['DB_NAME'] . "';";
            $this->link = new PDO($dns, $this->config['DB_USER'], base64_decode($this->config['DB_PASSWORD']));

            return $this;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @param $sql
     * @param array $values
     * @return mixed
     */
    public function execute($sql, $values = [])
    {
        $sth = $this->link->prepare($sql);

        return $sth->execute($values);
    }


    /**
     * @param $sql7
     * @param array $values
     * @return array
     */
    public function query($sql, $values = [], $statement = PDO::FETCH_OBJ)  //$statement = PDO::FETCH_OBJ
    {
        $sth = $this->link->prepare($sql);
        $sth->execute($values);

        $result = $sth->fetchAll($statement); //fetchAll(PDO::FETCH_ASSOC);

        if ($result === false) {
            return [];
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function lastInsertId()
    {
        return $this->link->lastInsertId();
    }
}
class GetConfig
{
    public static function getSettings()
    {
        $dotenvLoad = Dotenv::createImmutable(ROOT_DIR);
        $config = $dotenvLoad->load();

        return $config;
    }
}

$db = new Connection();
$config = new GetConfig();

//var_dump($_GET['dateload']);




/*$html = '<!DOCTYPE html>
<html lang="ru">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Дайджест</title>
</head>
<body>
<style>
  html {
      scroll-behavior: smooth;
      font-size: 100%;

  }

  body {
      font-family: Arial, Helvetica, sans-serif;
      padding: 0;
      margin: 0;
  }

  .container {
      padding: 20px;
      display: flex;
      flex-direction: row;
      justify-content: flex-start;
      align-items: flex-start;
      flex-wrap: wrap;
  }

  .head-info {
      position: absolute;
      z-index: 1;
      left: 50%;
      top: 50%;
      transform: translateX(-50%) translateY(-50%);
      color: white;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 4rem;
      text-align: center;
  }

  .head {
      position: relative;
      display: block;
      background: #4BA6C1 no-repeat top center;
      background-attachment: fixed;
      height: 100vh;
      background-size: cover;
  }

  .head-date {
      position: absolute;
      right: 0;
      top: 0;
      font-size: 1.5rem;
      color: white;
      margin: 16px;

  }

  .head-logo {
      filter: brightness(0) invert(1);
      position: absolute;
      width: 10rem;
      margin: 10px;
  }

  .svgHeader {
      position: absolute;
      left: 50%;
      bottom: 0;
      transform: translateX(-50%) translateY(-50%);
  }

  .menuSvg {
      position: absolute;
      cursor: pointer;
      left: 0;
      z-index: 100;
      transition-property: all;
      transition-duration: 300ms;
  }

  .svgNews {
      position: absolute;
      left: 50%;
      bottom: -45px;
      transform: translateX(-50%) translateY(-50%);
  }

  .svgImgLeft {
      position: absolute;
      left: -10px;
      top: -10px;
  }

  .svgImgRight {
      position: absolute;
      right: -10px;
      top: -10px;
  }

  .wrapper {
      padding: 20px;
  }

  #menuBody {
      background-color: white;
      overflow: auto;
      z-index: 2;
      position: fixed;
  }

  .menu {
      -webkit-box-shadow: 0px -6px 47px -1px rgba(0, 0, 0, 0.33);
      -moz-box-shadow: 0px -6px 47px -1px rgba(0, 0, 0, 0.33);
      box-shadow: 0px -6px 47px -1px rgba(0, 0, 0, 0.33);
  }

  .menu-title {
      position: relative;
      z-index: 5;
      background-color: rgb(75, 166, 193);
      color: white;
      font-family: Arial, Helvetica, sans-serif;
      box-shadow: 0px 3px 17px -4px rgba(0, 0, 0, 0.33);
      font-size: 1.5rem;
      padding: 5px 10px;
      text-align: right;
      height: 1.8rem;
  }

  .menu-title-fixed {
      background-color: rgb(75, 166, 193);
      color: white;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 1.5rem;
      padding: 5px 10px;
      box-shadow: 0px 3px 17px -4px rgba(0, 0, 0, 0.33);
      text-align: right;
      z-index: 10;
      position: fixed;
      top: 0;
      display: none;
      height: 1.8rem;
  }

  .menu-body {
      overflow: hidden;
  }

  .menu-section {
      flex-grow: 1;
  }

  .menu-section-title {
      padding: 10px;
      border-bottom: 1px solid rgba(0, 0, 0, 0.1);
      font-family: Arial, Helvetica, sans-serif;
      font-size: 1.5rem;
      color: #4BA6C1;
  }

  .menu-section-body {
      padding: 0;
      margin: 0;
      list-style: none;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 1.2rem;
  }

  .menu-section-body a {
      text-decoration: none;
      color: #000;
  }

  .menu-section-body a:visited {
      color: #000;
  }

  .menu-section-body a:hover {
      text-decoration: none;
  }

  .menu-logo {
      position: absolute;
      right: 10px;
      filter: brightness(0) invert(1);
      width: 6rem;
  }

  .news-section {
      z-index: -100;
  }

  li {
      border-bottom: 1px solid rgba(0, 0, 0, 0.1);
      padding: 15px 15px 15px 30px;
      transition-property: all;
      transition-duration: 100ms;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 1rem;
  }

  li:hover {
      background: #4BA6C1;
      color: white;
      cursor: pointer;
  }

  .news-section-title {
      background-color: #4BA6C1;
      color: white;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 2rem;
      padding: 10px 20px;
  }

  .news-section-bamk {
      color: #4BA6C1;
      font-family: Arial, Helvetica, sans-serif;
      text-transform: uppercase;
      font-size: 1.6rem;
  }

  .flex-reverse {
      -webkit-flex-direction: row-reverse;
      -ms-flex-direction: row-reverse;
      flex-direction: row-reverse;
  }

  .flex-no-reverse {
      -webkit-flex-direction: row;
      -ms-flex-direction: row;
      flex-direction: row;
  }

  .new-section-bank-body {

      display: -ms-flexbox;
      display: -webkit-flex;
      display: flex;
      flex-wrap: wrap;
      -webkit-justify-content: flex-start;
      -ms-flex-pack: start;
      justify-content: flex-start;
      -webkit-align-content: flex-start;
      -ms-flex-line-pack: start;
      align-content: flex-start;
      align-items: stretch;
      -webkit-align-items: stretch;
      -ms-flex-align: stretch
  }

  .new-section-bank-text {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 1.2rem;
      padding: 10px 20px;
      flex-grow: 2;
      width: 200px;
  }

  .new-section-bank-img {
      position: relative;
      margin: 10px 20px;
      flex-grow: 1;
      width: 200px;
  }

  .news-section-body {
      position: relative;
      padding: 30px 0;
  }

  .new-section-bank-text span {
      color: #069;
  }

  a {
      text-decoration: none;
      color: #069;
  }

  a:hover {
      text-decoration: underline;
  }

  a:visited {
      color: #069;
  }

  .head-icon {
      padding: 5px;
  }

  .head-icon a {
      padding: 5px;
  }

  .head-icon i {
      color: white;
  }

  .links {
      position: relative;
      top: -40px;
  }

  #swap {
      z-index: 100;
      position: fixed;
      background: #4BA6C1;
      transform: rotate(270deg) translateY(100%);
      margin: 10px 10px;
      width: 30px;
      height: 30px;
      opacity: 0.3;
      cursor: pointer;
      border-radius: 5px;
      transition: all 0.2s ease-out;
      bottom: 0px;
      right: 0;
  }

  #swap path {
      fill: #fff;
  }

  #swap:hover {
      opacity: 1;
  }

  #calendar-digest {
      z-index: 100;
      right: 0;
      background: white;
      position: fixed;
      width: 200px;
      box-shadow: 0px 3px 17px -4px rgba(0, 0, 0, 0.33);
  }

  .calendar-head {
      color: white;
      background: #4BA6C1;
      text-align: center;
      display: flex;
      flex-direction: row;
      flex-wrap: nowrap;
      justify-content: space-between;
      align-items: center;
  }

  .calendar-head span {
      margin: 5px 10px;
  }

  .calendar-body {
      display: flex;
      flex-direction: row;
      flex-wrap: wrap;
      justify-content: center;
      border: 1px solid gray;
  }

  .calendar-body-elem {
      color: black;
      cursor: pointer;
      margin: 5px;
      padding: 2px;
      border: 1px solid gray;
  }

  .calendar-body-elem-aсtive {
      color: white;
      background: #4BA6C1;
      cursor: pointer;
      margin: 5px;
      padding: 2px;
      border: 1px solid gray;
  }


  .link_none:hover {
      text-decoration: none;
  }

  .container{
      display: flex;
      justify-content: center;
      align-items: flex-start;
      width: 100%;
      height: auto;
      flex-flow: row nowrap;
      margin: 0;
      padding: 0;
  }
  .left-block, .right-block {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      width:30%;
      height: auto;
  }
  .center-block-left, .center-block-right {
      display: flex;
      justify-content: flex-start;
      align-items: flex-start;
      width: 70%;
      height: auto;
      padding: 0 15px 15px 15px;
  }

  .center-block-left {
      margin-left: 30%;
  }
  .center-block-right {
      margin-right: 30%;
  }

  .news-section-title {
      margin-bottom: 20px;
  }
</style>

<div class="news-section">
  <div class="news-section-title">Встреча с органами власти</div>

  <div class="container">
      <div class="left-block">
          <img style="width: 100%;" src="http://exportpdf.cc/shvYV9okSQc.jpg" alt="">
      </div>
      <div class="center-block-left">
          <p><a href="http://kazan-news.net/politics/2020/11/27/272358.html" target="_blank">Сбер в Казани провёл
              онлайн-конференцию «Цифровые тенденции в медицине для представителей сферы здравоохранения
              Республики Татарстан.</a> В мероприятии приняли участие Президент Республики Рустам Минниханов,
              заместитель Председателя Правления Сбербанка Ольга Голодец, Вице-президент-Председатель
              Волго-Вятского банка Петр Колтыпин. Темой конференции стало будущее цифровой медицины: как
              искусственный интеллект может помочь врачам, какие законы нужны, чтобы телемедицина стала
              эффективнее. В рамках мероприятия Рустам Минниханов и Ольга Голодец наградили лучших татарстанских
              врачей за активное участие в реализации проектов в сфере цифровизации медицинских услуг.Также в
              рамках конференции Петр Колтыпин и ректор Казанского федерального университета Ильшат Гафуров
              подписали соглашение о стратегическом партнерстве в сфере образования и здравоохранения. Заключенное
              соглашение предусматривает взаимодействие в области здравоохранения, образования, науки, разработки
              и реализации профессиональных образовательных программ, а также по вопросам проведения совместных
              исследований и реализации научно-исследовательских проектов, программ и решений в сфере
              здравоохранения. Казанский федеральный университет представил на конференции свой проект
              «Универсальная медицинская информационная система (МИС) на базе ИИ-решений для медицинских
              loT-устройств и диагностической техники». Проект представляет собой платформенное решение,
              совместимое с облачной платформой SberCloud и суперкомпьютером «Кристофари». В состав платформы
              входит единая интеграционная шина для подключаемых систем и ряд алгоритмов программных стеков для
              поддержки принятия решения, а также интеллектуальной диагностики. В продолжение мероприятия Рустам
              Минниханов встретился в Доме Правительства с Ольгой Голодец, где участники обсудили вопросы
              внедрения цифровых продуктов Сбера в сферу здравоохранения Татарстана и дальнейшие шаги реализации
              проектов по цифровизации. В рамках встречи Рустам Минниханов вручил Ольге Голодец медаль «100 лет
              образования ТАССР» за существенный вклад в укрепление социально-экономического потенциала Республики
          </p>
      </div>
  </div>



  <div class="container">
      <div class="left-block">
          <img style="width: 100%;" src="http://pilots.cc/uploads/product[81]new_wallpapers206481-5f5b5e72cd4ac.jpg" alt="">
      </div>
      <div class="center-block-left">
          <p><a href="http://kazan-news.net/politics/2020/11/27/272358.html" target="_blank">Сбер в Казани провёл
              онлайн-конференцию «Цифровые тенденции в медицине для представителей сферы здравоохранения
              Республики Татарстан.</a> В мероприятии приняли участие Президент Республики Рустам Минниханов,
              заместитель Председателя Правления Сбербанка Ольга Голодец, Вице-президент-Председатель
              Волго-Вятского банка Петр Колтыпин. Темой конференции стало будущее цифровой медицины: как
              искусственный интеллект может помочь врачам, какие законы нужны, чтобы телемедицина стала
              эффективнее. В рамках мероприятия Рустам Минниханов и Ольга Голодец наградили лучших татарстанских
              врачей за активное участие в реализации проектов в сфере цифровизации медицинских услуг.Также в
              рамках конференции Петр Колтыпин и ректор Казанского федерального университета Ильшат Гафуров
              подписали соглашение о стратегическом партнерстве в сфере образования и здравоохранения. Заключенное
              соглашение предусматривает взаимодействие в области здравоохранения, образования, науки, разработки
              и реализации профессиональных образовательных программ, а также по вопросам проведения совместных
              исследований и реализации научно-исследовательских проектов, программ и решений в сфере
              здравоохранения. Казанский федеральный университет представил на конференции свой проект
              «Универсальная медицинская информационная система (МИС) на базе ИИ-решений для медицинских
              loT-устройств и диагностической техники». Проект представляет собой платформенное решение,
              совместимое с облачной платформой SberCloud и суперкомпьютером «Кристофари». В состав платформы
              входит единая интеграционная шина для подключаемых систем и ряд алгоритмов программных стеков для
              поддержки принятия решения, а также интеллектуальной диагностики. В продолжение мероприятия Рустам
              Минниханов встретился в Доме Правительства с Ольгой Голодец, где участники обсудили вопросы
              внедрения цифровых продуктов Сбера в сферу здравоохранения Татарстана и дальнейшие шаги реализации
              проектов по цифровизации. В рамках встречи Рустам Минниханов вручил Ольге Голодец медаль «100 лет
              образования ТАССР» за существенный вклад в укрепление социально-экономического потенциала Республики
          </p>
      </div>
  </div>
</div>
</body>
</html>';*/


    /*
use Dompdf\Dompdf;
use Dompdf\Options;
require_once("Dompdf/autoload.inc.php");
$dompdf = new DOMPDF();
$dompdf->load_html($html);
$dompdf->set_paper('a4', 'portrait');
$dompdf->render();
$dompdf->stream("newfile.pdf");*/