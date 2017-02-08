# MajorDomo-AZS
Цены на топливо от АЗС "Авиас" для Украины.

Модуль предназначен для системы домашней автоматизации MajorDomo, он получает и записывает в БД стоимость для видов топлива:

A92
A95
A95E
DT
GAZ

Можно выбрать все виды топлива, можно только одну интересующую позицию. Так же стоимость можно выбрать в зависимости от области в которой вы проживаете. 
Для того что бы озвучить курс валют (у меня это происходит автоматически каждое утро в заданное время, либо по команде "курс валют"), необходимо создать сценарий и шаблон поведения.
Либо самостоятельно настроить вывод нанных из пользую интерфейс самого MajorDomo. Все данных хранятся в классе "AZS".
