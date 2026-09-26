---
title: "Virtual Functions II"
date: 2023-01-26T13:13:10+05:30
description: A dive into abstract types
tags: [CPP, virtual function, programming]
---

In the previous [post](/posts/2023-01-22-virtualfunc1/) we saw one basic use-case of a **virtual function**. Here is another awesome use case involving the **virtual** keyword.

In the previous post, there was a section on why we should group objects and types based on their common functionalities. The example we took is a typical game where almost all data types have a function to update and render. What if someone forgot to do it? How to enforce this as a rule?

## Pure virtual function
A virtual function becomes a pure virtual method (*functions of a type (class/struct) are called methods*) when we declare it and assign a `0` to the declaration, like this.

```c++
class Actor {
    virtual void render() = 0;
    virtual void update() = 0;
};
```
This means, there can be no definition for the function `render()` in the scope of `Actor` type. The function `render()` cannot be defined under actor at all!
```c++
/// in the cpp file
void Actor::render() {
    ///blah blah blah ...
}
```
Defining the function and creating an object for `Actor` results in a compiler error. 
```c++
void main() {
    Actor actor;
}
```
> Think about it this way, the compiler **bars** us from defining a pure virtual method. Thus `Actor` is not going to have any definition for the methods `render()` and `update()`. If there is no definition of a method, what will the program do if a call to that function exists? The program will crash. Thus, there is no object and no call is possible!

This is the basis of a pure virtual function. If there cannot be a definition of a method and there cannot be an object for a type, naturally you will wonder about the use of this feature. 
## Need for a pure virtual function
If a class has a pure virtual function, then it becomes an **abstract class**. An abstract class is like a dictator who dictates the rules for deriving from it. Therefore, in this example, `Actor` expects all the classes to implement the pure virtual functions if they are derived from it. This is also called the implementation of the class `Actor`. The method that has the definition for the pure virtual method defined in the base class is `overriding` the method. In this example, the `render()` and `update()` methods of `Player` and `Monster` is `overriding` their declaration in `Actor` class.

As we have defined this rule, we are free to call the methods `render()` and `update()` without worrying about the actual type of object that we are referring to. Considering `Player` and `Monster` are derived from `Actor` we can do the following.
```c++
int main(){
    // initialize game
    // ...
    Actor* actors [] = {
        new Player(),
        new Monster(),
        new Monster()
    };

    while(!exitGame()) {
        // game update
        for(auto a : actors){
            a->update();
        }

        // game render
        for(auto a : actors){
            a->render();
        }
    }
}
```
The same rule that we discussed in the previous post on virtual functions stands true i.e., even though we cannot create an object for the class `Actor`, hold the references of all the objects of derived classes under the base class as pointers.
## Multi-level overriding
The modern C++ compiler expects us to mark the method definitions with an `override` keyword. Though this is not mandatory, this helps the compiler and us in many ways. One of the uses for us is, it helps us to differentiate the methods that are overridden from the local/unique methods. 

> To visually differentiate it easily, previously few devs (including me) keep reusing the `virtual` keyword even in the derived classes, even though it is unnecessary. Now it is easy to differentiate between `virtual` ones and local ones. 

However, it is optional to use both keywords, but it is a good practice to use the `override` keyword!

Coming back to multi-level overriding. If we have a special kind of `Monster` called the `Cyclops` who has a good deal of uniqueness compared to all other `Monster`s. For example, let's say that the damage a `Cyclops` can cause is heavy and, you have to send ripples of damage signals to neighboring elements of the game. To encode this unique ability we can define the `update()` method in the class `Cyclops` and let the compiler & linker handle the rest! This call to `render()` and `update()` does what we had expected it to do.

```c++
class Player : public Actor {
    virtual void render() override {
        printf("Player render\n");
        // render player stuff
    }

    virtual void update() override {
        printf("Player update\n");
        // update player stuff
    }

    // local/unique members and methods
};

class Monster : public Actor {
    virtual void render() override {
        printf("Monster render\n");
        // render monster stuff
    }

    virtual void update() override {
        printf("Monster update\n");
        // update monster stuff
    }
    
    // local/unique members and methods
};

class Cyclops : public Monster {
    
    virtual void update() override {
        printf("Cyclops update: ");
        printf("Emit damage signal\n");
        // update Cyclops stuff
    }
    
    // local/unique members and methods
}

int main(){
    // initialize game
    // ...
    Actor* actors [] = {
        new Player(),
        new Monster(),
        new Cyclops(),
        new Monster()
    };

    while(!exitGame()) {
        // game update
        printf("\n***Game update***\n");
        for(auto a : actors){
            a->update();
        }

        printf("\n***Game render***\n");
        // game render
        for(auto a : actors){
            a->render();
        }
    }
}
```
If the program is executed, we get the following output, repeatedly until the game exits.
```c++

***Game update***
Player update
Monster update
Cyclops update: Emit damage signal
Monster update

***Game render***
Player render
Monster render
Monster render
Monster render

***Game update***
Player update
Monster update
Cyclops update: Emit damage signal
Monster update

***Game render***
....

```
Notice how the `update()` calls go to `Cyclops`'s method whereas all the `render()` call goes to the `Monster`. Our problem of passing messages to the neighboring elements is easily solved and our core game loop remains *fairly unaffected*. 

[gist of example](https://gist.github.com/madptr/845008cdec91068e3b2caa200a53e02c#file-virtualfunc2-abstractclass-cpp)

Was this useful? Let me know on [Twitter](https://twitter.com/madptr) or [Mastodon](https://mastodon.gamedev.place/@madptr)!

[To be continued...](/posts/2023-02-15-virtualfunc3/)