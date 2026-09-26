---
title: "Virtual Functions I"
date: 2023-01-22T16:45:51+05:30
description: A dive into virtual functions
tags: [CPP, virtual function, programming]
---

Have you ever come across the keyword `virtual` in a CPP class and felt that you haven't fully understood it yet?
In this post, I'll try to explain one basic use case where `virtual` helps us. This is a three part series. The links are [Part-II](/posts/2023-01-26-virtualfunc2/) & [Part-III](/posts/2023-02-15-virtualfunc3/).

## Why do we need different types?
Let's understand why we need to create different types. The need for different types arises because of the uniqueness of their behavior itself. If such differences can be controlled or managed by using a few variables we can refrain from creating a new type. 

Take the example of a chess game. Looking at the game we may think that there is a need for creating a `King` `Queen` `Rook` `Bishop` `Knight` and `Pawn` as different types. If we observe the problem a little closer, if we can control how many tiles each piece can move and which directions are possible, the behavior of `King` `Queen` `Rook` `Bishop` and `Pawn` can be controlled. Leaving us with just two types, a `ChessPiece` and a `Knight`. Even now, if we can encode the movement of `Knight` within the system we created for `ChessPiece`, we can continue with just one data type.

On the other hand, take the example of a cricket game. Is it possible to control the behavior of `Bat` `Ball` `Pitch` `Batsman` `Bowler` `Umpire` etc. using the same type? If it can be done we can do away with all these types listed. We create different types only to encode the behavioral differences of a certain type from the other types. 
**type* here refers to a `class` or a `struct`*
## Need for a common base type
Let's consider there are two types `Player` and `Monster`. 
If there is a question on the need for `Player` and `Monster`, let's assume that the behavior of `Player` is completely different from the `Monster`. In a typical game, the `Player` responds to the user input whereas the `Monster` does not. Maybe the `Monster` can pass messages to other *monsters* in the system/game world and coordinate an attack.
Likewise, we can keep listing endless differences between the two. 

```c++
class Player {
    // members and methods
};

class Monster {
    // members and methods
};
```
Here, all the objects of `Monster` and the `Player` cannot be stored under a variable of some type. 

```c++
<what_type_is_items?> items [] = {
    Player(),
    Monster(),
    Monster()
}

```
Sure we cannot do something like the above and are forced to maintain a variable for each type we create. Consider creating a roguelike game, with 100s of different monsters. As a programmer, we will be lost in a sea of variables. For ease of handling and to improve the flexibility of the program, we derive them from a common base type. Let's create a base type called `Actor` and derive `Player` and `Monster` from it.

```c++
class Actor {
    // common members and methods
};

class Player : public Actor {
    // local/unique members and methods
};

class Monster : public Actor {
    // local/unique members and methods
};
```
## Grouping based on common functionality
But from where the need arises that we should refer to all objects using a single array variable? If they are all going to behave differently, why should we do this at all?

In a game, almost all the objects need to do these two things much more frequently. Almost all the objects should be calling their `update()` and `render`()` methods. This is a single behavior that is common for almost all the data types in a game. It is the single factor that unites all data types. Also, it will be pretty hard to keep adding a separate update and render calls for every single type we add to our game. Thus, if we can refer to everything using a single array, or list we don't have to be reminded of it whenever we add a new element to the game.

Eventually, we can do the following and access all the objects without worrying about their 'actual' type. 

```c++
int main(){
    Actor* actors [] = {
        new Player(),
        new Monster(),
        new Monster()
    };

    for(auto a : actors){
        a->doStuff();
    }
}
```
Here, `Actor* actors []` is an array of addresses of the type `Actors`.
This `actors` array can hold any number of 'address of variables' of different types, *if and only if* they are derived from its own type. Here we can store the address of a variable of type `Player` followed by two addresses of variables of type `Monster` as `Actor`.

<!--- How sure are we that all the objects in the array `Actor` have a method `doStuff()`? If the compiler figures out that this function call is not valid i.e. there is no method to call at this point, it will generate a compiler error. If it compiles, it works! no worries.
--->

## A thing with unique behavior
What if some `Actor` behaves differently?

Let's consider we have a type called `Car`.

```c++
class Car : public Actor {
    // local/unique members and methods in relation to Actor
    ...
    // common members and methods related to Car
    int getNumDoors();
    // other common members and methods related to Car
};

class Fiat : public Car {
    // local/unique members and methods
};

class Rover : public Car {
    // local/unique members and methods
};
```
>Here `Fiat` and `Rover` are also a type of `Actor` because they inherit the `Actor`'s properties via `Car` which is derived from the `Actor`.

Since all the cars in our game are going to have 4 doors, let us consider we have implemented it in the base type's scope, and it just returns `4`. 
Now, we got to introduce a sports car into the game. 
Alas! the `getNumDoors()` of the `Car` is defined to return 4 doors only! 
Now, we have a few options ahead.

1. Introduce a variable `numDoors` in the base class and set its value, maybe via a constructor.
2. Move the `getNumDoors()` function from the base class to the derived classes.
3. Introduce virtual function.

>If you feel **option 1** works perfectly fine, it must be because of my bad example. Handling one integer is easy. Assume the integer is a placeholder that presents a problem bigger than an integer.

While the first two methods are bound to work fine, it demands a lot of code refactorings. This is where a **virtual function** comes to the rescue. 

```c++
class Car : public Actor {
    // local/unique members and methods in relation to Actor
    ...
    // common members and methods related to Car
    virtual int getNumDoors(){ return 4; }
    // other common members and methods related to Car
};

class Fiat : public Car {
    // local/unique members and methods
};

class Rover : public Car {
    // local/unique members and methods
};

class SportsCar : public Car {
    // local/unique members and methods
    virtual int getNumDoors(){ return 2; }
};

void main() {
    Car* cars [] = {
        new Fiat(),
        new Rover(),
        new Car(),
        new SportsCar()
    };

    for(auto c : cars){
        c->getNumDoors();
    }
}
```
As the function `getNumDoors()` has been changed to a `virtual` function, the compiler is forced to link this call to the definition of a derived type if exists. This is a very useful feature at times like this. 

I'm just getting started... Was this useful? Let me know your thoughts on [Twitter](https://twitter.com/madptr) or [Mastodon](https://mastodon.gamedev.place/@madptr)!

[To be continued...](/posts/2023-01-26-virtualfunc2/)